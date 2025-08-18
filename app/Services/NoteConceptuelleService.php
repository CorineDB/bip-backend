<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\NoteConceptuelleResource;
use App\Models\NoteConceptuelle;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\NoteConceptuelleRepositoryInterface;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Services\Contracts\NoteConceptuelleServiceInterface;
use App\Enums\StatutEvaluationNoteConceptuelle;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use App\Models\Evaluation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class NoteConceptuelleService extends BaseService implements NoteConceptuelleServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected DocumentRepositoryInterface $documentRepository;
    protected ProjetRepositoryInterface $projetRepository;
    protected EvaluationRepositoryInterface $evaluationRepository;

    public function __construct(
        NoteConceptuelleRepositoryInterface $repository,
        DocumentRepositoryInterface $documentRepository,
        ProjetRepositoryInterface $projetRepository,
        EvaluationRepositoryInterface $evaluationRepository
    )
    {
        parent::__construct($repository);

        $this->documentRepository = $documentRepository;
        $this->projetRepository = $projetRepository;
        $this->evaluationRepository = $evaluationRepository;
    }

    protected function getResourceClass(): string
    {
        return NoteConceptuelleResource::class;
    }

    protected function getResourcesClass(): string
    {
        return NoteConceptuelleResource::class;
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Extraire les données spécifiques au payload
            $champsData = $data['champs'] ?? [];
            $estSoumise = $data['est_soumise'] ?? false;
            $projetId = $data['projetId'] ?? null;

            if (!$projetId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID du projet requis.'
                ], 422);
            }

            // Déterminer le statut selon est_soumise
            $statut = $estSoumise ? 'soumise' : 'brouillon';

            // Préparer les données de la note conceptuelle
            $intitule = 'Note conceptuelle';
            if (isset($champsData['contexte_justification'])) {
                $intitule = substr($champsData['contexte_justification'], 0, 100) . '...';
            }

            // Convertir le statut en numérique selon l'enum de la table
            $statutNumeric = match($statut) {
                'soumise' => 1,
                'rejetee' => -1,
                default => 0 // brouillon
            };

            $noteData = [
                'intitule' => $intitule,
                'statut' => $statutNumeric,
                'note_conceptuelle' => $champsData,
                'rediger_par' => auth()->id(),
            ];

            // Chercher ou créer une note conceptuelle unique par projet
            $noteConceptuelle = $this->repository->getModel()
                ->where('projetId', $projetId)
                ->first();

            if ($noteConceptuelle) {
                // Mettre à jour la note existante
                $noteConceptuelle->update($noteData);
                $message = 'Note conceptuelle mise à jour avec succès.';
                $statusCode = 200;
            } else {
                // Créer une nouvelle note
                $noteData['projetId'] = $projetId;
                $noteConceptuelle = $this->repository->create($noteData);
                $message = 'Note conceptuelle créée avec succès.';
                $statusCode = 201;
            }

            // Récupérer le canevas de rédaction de note conceptuelle
            $canevasNoteConceptuelle = $this->documentRepository->getModel()->where([
                'type' => 'formulaire'
            ])->whereHas('categorie', function($query) {
                $query->where('slug', 'canevas-redaction-note-conceptuelle');
            })->orderBy('created_at', 'desc')->first();

            if ($canevasNoteConceptuelle) {
                // Sauvegarder les champs dynamiques basés sur le canevas
                $this->saveDynamicFieldsFromCanevas($noteConceptuelle, $champsData, $canevasNoteConceptuelle);
            }

            $noteConceptuelle->refresh();

            DB::commit();

            return (new $this->resourceClass($noteConceptuelle))
                ->additional(['message' => $message])
                ->response()
                ->setStatusCode($statusCode);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Sauvegarder les champs dynamiques basés sur le canevas de note conceptuelle
     */
    private function saveDynamicFieldsFromCanevas(NoteConceptuelle $noteConceptuelle, array $champsData, $canevasNoteConceptuelle): void
    {
        // Récupérer tous les champs du canevas de note conceptuelle
        $champsDefinitions = $canevasNoteConceptuelle->all_champs;

        // Indexer par attribut pour accès rapide
        $champsMap = $champsDefinitions->keyBy('attribut');

        $syncData = [];

        foreach ($champsData as $attribut => $valeur) {
            if (isset($champsMap[$attribut])) {
                $champ = $champsMap[$attribut];

                // Le cast JSON du modèle ChampProjet gère automatiquement tout type
                $syncData[$champ->id] = [
                    'valeur' => $valeur, // Peut être string, array, object, number, boolean, etc.
                    'commentaire' => null
                ];
            }
        }

        // Synchroniser tous les champs reçus
        if (!empty($syncData)) {
            $noteConceptuelle->champs()->sync($syncData);
        }
    }

    /**
     * Méthode de mise à jour simplifiée - utilise la logique de create
     */
    public function update($id, array $data): JsonResponse
    {
        try {
            // Récupérer la note conceptuelle pour obtenir le projetId
            $noteConceptuelle = $this->repository->findOrFail($id);

            // Ajouter le projetId aux données et utiliser la logique de create
            $data['projetId'] = $noteConceptuelle->projetId;

            // La méthode create gère déjà l'update automatiquement
            return $this->create($data);

        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }


    public function validateNote(int $projetId, int $noteId, array $data): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'dpaf') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            // Vérifier que le projet existe
            $projet = $this->projetRepository->findOrFail($projetId);

            // Trouver la note conceptuelle associée au projet
            $noteConceptuelle = $this->repository->getModel()->where([
                'id' => $noteId,
                'projetId' => $projetId
            ])->first();

            if (!$noteConceptuelle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note conceptuelle non trouvée pour ce projet.'
                ], 404);
            }

            if ($projet->statut->value != StatutIdee::VALIDATION_PROFIL->value) {
                throw new Exception("Le projet n'est pas a l'etape de validation");
            }

            switch ($data["decision"]) {
                case 'a_maturite':
                    $projet->update([
                        'statut' => StatutIdee::PRET,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::PRET),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::PRET),
                        'type_projet' => TypesProjet::simple
                    ]);
                    break;
                case 'faire_etude_prefaisabilite':
                    $projet->update([
                        'statut' => StatutIdee::TDR_PREFAISABILITE,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                        'type_projet' => TypesProjet::complexe1
                    ]);
                    break;
                case 'reviser_note_conceptuelle':
                    $projet->update([
                        'statut' => StatutIdee::NOTE_CONCEPTUEL,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                        'type_projet' => TypesProjet::simple
                    ]);
                    break;

                case 'abandonner_projet':
                    $projet->update([
                        'statut' => StatutIdee::ABANDON,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::ABANDON),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::ABANDON),
                        'type_projet' => TypesProjet::simple
                    ]);
                    break;

                case 'sauvegarder':
                    # code...
                    break;
                default:
                    # code...
                    break;
            }


                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($projet, $projet->statut);
                $this->enregistrerDecision($projet, "Validation de l'etude de profil par la dpaf", $attributs["commentaire"] ?? 'Idée transformée en projet');

            /*

            // Vérifier s'il existe une évaluation précédente validée
            $evaluationPrecedente = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'validation-idee-projet-a-projet')
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            // Validation idee de projet
            $evaluation = Evaluation::create([
                'type_evaluation' => 'validation-idee-projet-a-projet',
                'projetable_type' => get_class($ideeProjet),
                'projetable_id' => $ideeProjet->id,
                'date_debut_evaluation' => now(),
                'date_fin_evaluation' => now(),
                'valider_le' =>  now(),
                'evaluateur_id' => auth()->user()->id,
                'valider_par' => auth()->user()->id,
                'commentaire' => $attributs["commentaire"],
                'evaluation' => $attributs,
                'resultats_evaluation' => $attributs["decision"],
                'statut' => 1,
                'id_evaluation' => $evaluationPrecedente ? $evaluationPrecedente->id : null
            ]); */

            // Valider la note conceptuelle (statut 1 = validée/soumise)
            $noteConceptuelle->update([
                'statut' => 1,
                'valider_par' => auth()->id(),
            ]);

            DB::commit();

            return (new $this->resourceClass($noteConceptuelle))
                ->additional(['message' => 'Note conceptuelle validée avec succès.'])
                ->response()
                ->setStatusCode(200);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function getValidationDetails(int $projetId, int $noteId): JsonResponse
    {
        try {
            // Vérifier que le projet existe
            $projet = $this->projetRepository->findOrFail($projetId);

            // Trouver la note conceptuelle associée au projet avec les détails de validation
            $noteConceptuelle = $this->repository->getModel()->where([
                'id' => $noteId,
                'projetId' => $projetId
            ])->with(['validateur'])->first();

            if (!$noteConceptuelle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note conceptuelle non trouvée pour ce projet.'
                ], 404);
            }

            $validationDetails = [
                'statut' => $noteConceptuelle->statut,
                'validateur' => $noteConceptuelle->validateur,
                'decision' => $noteConceptuelle->decision,
            ];

            return response()->json([
                'success' => true,
                'data' => $validationDetails
            ], 200);

        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Créer une grille d'évaluation basée sur le canevas de note conceptuelle
     */
    public function creerGrilleEvaluation(NoteConceptuelle $noteConceptuelle): array
    {
        // Récupérer le canevas de note conceptuelle
        $canevas = $this->documentRepository->getModel()
            ->where('type', 'formulaire')
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-redaction-note-conceptuelle'))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$canevas) {
            throw new Exception('Canevas de note conceptuelle introuvable');
        }

        $grille = [];
        $champsCanevas = $canevas->all_champs;
        $noteData = $noteConceptuelle->note_conceptuelle ?? [];

        foreach ($champsCanevas as $champ) {
            // Ne pas évaluer certains types de champs
            if (in_array($champ->type_champ, ['section', 'group'])) {
                continue;
            }

            $valeurRedige = $noteData[$champ->attribut] ?? null;

            // Formater la valeur selon le type de champ
            $contenuRedige = $this->formaterContenuRedige($valeurRedige, $champ);

            $critere = [
                'champ_id' => $champ->id,
                'label' => $champ->label,
                'attribut' => $champ->attribut,
                'type_champ' => $champ->type_champ,
                'is_required' => $champ->is_required,
                'description' => $champ->info ?? "Évaluation de: {$champ->label}",
                'poids' => $this->calculatePoids($champ),
                'contenu_redige' => $contenuRedige, // Le contenu rédigé à évaluer
                'valeur_brute' => $valeurRedige, // Valeur brute pour référence
                'statut_evaluation' => null, // Options: passe, retour, non_accepte
                'commentaire_evaluateur' => null, // Commentaire de l'évaluateur
                'options_notation' => $this->getOptionsNotation($canevas),
                'a_du_contenu' => !empty($valeurRedige) && $valeurRedige !== '' && $valeurRedige !== null
            ];

            $grille[] = $critere;
        }

        return $grille;
    }

    /**
     * Récupérer les options de notation depuis la configuration du canevas
     */
    private function getOptionsNotation($canevas): array
    {
        $evaluationConfigs = $canevas->evaluation_configs ?? [];

        // Si la configuration existe dans le canevas, l'utiliser
        if (!empty($evaluationConfigs['options_notation'])) {
            return $evaluationConfigs['options_notation'];
        }

        // Sinon, utiliser les options par défaut
        return StatutEvaluationNoteConceptuelle::options();
    }

    /**
     * Calculer le poids d'un critère selon son importance
     */
    private function calculatePoids($champ): int
    {
        // Critères importants ont plus de poids
        $criteresImportants = [
            'contexte_justification',
            'objectifs_projet',
            'resultats_attendus',
            'budget_detaille',
            'cout_estimatif_projet'
        ];

        if (in_array($champ->attribut, $criteresImportants)) {
            return $champ->is_required ? 5 : 3;
        }

        return $champ->is_required ? 3 : 1;
    }

    /**
     * Récupérer la valeur d'un champ depuis la note conceptuelle
     */
    private function getValeurFromNote(NoteConceptuelle $noteConceptuelle, string $attribut)
    {
        $noteData = $noteConceptuelle->note_conceptuelle ?? [];
        return $noteData[$attribut] ?? null;
    }

    /**
     * Créer une évaluation pour une note conceptuelle
     */
    public function creerEvaluation(int $noteConceptuelleId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $noteConceptuelle = $this->repository->findOrFail($noteConceptuelleId);

            // Vérifier qu'il n'existe pas déjà une évaluation en cours
            $evaluationExistante = $this->evaluationRepository->getModel()
                ->where('projetable_type', NoteConceptuelle::class)
                ->where('projetable_id', $noteConceptuelle->id)
                ->where('type_evaluation', 'note_conceptuelle')
                ->first();

            if ($evaluationExistante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une évaluation existe déjà pour cette note conceptuelle.'
                ], 422);
            }

            // Générer la grille d'évaluation
            $grille = $this->creerGrilleEvaluation($noteConceptuelle);

            // Créer l'évaluation
            $evaluationData = [
                'type_evaluation' => 'note_conceptuelle',
                'projetable_type' => NoteConceptuelle::class,
                'projetable_id' => $noteConceptuelle->id,
                'evaluateur_id' => $data['evaluateur_id'] ?? auth()->id(),
                'date_debut_evaluation' => now(),
                'evaluation' => $grille,
                'statut' => 0, // En cours
                'commentaire' => $data['commentaire'] ?? null
            ];

            $evaluation = $this->evaluationRepository->create($evaluationData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Évaluation créée avec succès.',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'grille' => $grille,
                    'statuts_disponibles' => StatutEvaluationNoteConceptuelle::options()
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Mettre à jour une évaluation
     */
    public function mettreAJourEvaluation(int $evaluationId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $evaluation = $this->evaluationRepository->findOrFail($evaluationId);

            // Mettre à jour la grille avec les statuts d'évaluation
            $grilleActuelle = $evaluation->evaluation ?? [];
            $nouvelleGrille = [];

            foreach ($grilleActuelle as $index => $critere) {
                $critereId = $critere['champ_id'];

                if (isset($data['evaluations'][$critereId])) {
                    $critere['statut_evaluation'] = $data['evaluations'][$critereId]['statut'];
                    $critere['commentaire'] = $data['evaluations'][$critereId]['commentaire'] ?? null;
                }

                $nouvelleGrille[] = $critere;
            }

            // Calculer le résultat global
            $resultat = $this->calculerResultat($nouvelleGrille);

            $updateData = [
                'evaluation' => $nouvelleGrille,
                'resultats_evaluation' => $resultat,
                'statut' => $data['finaliser'] ?? false ? 1 : 0,
                'commentaire' => $data['commentaire_global'] ?? $evaluation->commentaire
            ];

            if ($data['finaliser'] ?? false) {
                $updateData['date_fin_evaluation'] = now();
                $updateData['valider_par'] = auth()->id();
                $updateData['valider_le'] = now();
            }

            $evaluation->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Évaluation mise à jour avec succès.',
                'data' => [
                    'evaluation' => $evaluation->fresh(),
                    'resultat' => $resultat
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Calculer le résultat global de l'évaluation
     */
    private function calculerResultat(array $grille): array
    {
        $totalPoids = 0;
        $poidsPassé = 0;
        $critèresParStatut = [
            'passe' => 0,
            'retour' => 0,
            'non_accepte' => 0,
            'non_evalué' => 0
        ];

        foreach ($grille as $critere) {
            $poids = $critere['poids'] ?? 1;
            $totalPoids += $poids;

            $statut = $critere['statut_evaluation'] ?? 'non_evalué';

            if ($statut === 'passe') {
                $poidsPassé += $poids;
            }

            $critèresParStatut[$statut] = ($critèresParStatut[$statut] ?? 0) + 1;
        }

        $pourcentageReussite = $totalPoids > 0 ? round(($poidsPassé / $totalPoids) * 100, 2) : 0;

        // Déterminer le statut global
        $statutGlobal = 'en_cours';
        if ($critèresParStatut['non_evalué'] === 0) {
            if ($critèresParStatut['non_accepte'] > 0) {
                $statutGlobal = 'non_accepte';
            } elseif ($critèresParStatut['retour'] > 0) {
                $statutGlobal = 'retour';
            } else {
                $statutGlobal = 'passe';
            }
        }

        return [
            'statut_global' => $statutGlobal,
            'pourcentage_reussite' => $pourcentageReussite,
            'total_criteres' => count($grille),
            'criteres_par_statut' => $critèresParStatut,
            'poids_total' => $totalPoids,
            'poids_passe' => $poidsPassé
        ];
    }

    /**
     * Récupérer l'évaluation d'une note conceptuelle
     */
    public function getEvaluation(int $noteConceptuelleId): JsonResponse
    {
        try {
            $noteConceptuelle = $this->repository->findOrFail($noteConceptuelleId);

            $evaluation = $this->evaluationRepository->getModel()
                ->where('projetable_type', NoteConceptuelle::class)
                ->where('projetable_id', $noteConceptuelle->id)
                ->where('type_evaluation', 'note_conceptuelle')
                ->with(['evaluateur', 'validateur'])
                ->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune évaluation trouvée pour cette note conceptuelle.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => $evaluation,
                    'statuts_disponibles' => StatutEvaluationNoteConceptuelle::options()
                ]
            ]);

        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Configurer les options de notation pour l'évaluation des notes conceptuelles
     */
    public function configurerOptionsNotation(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Récupérer le canevas de note conceptuelle
            $canevas = $this->documentRepository->getModel()
                ->where('type', 'formulaire')
                ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-redaction-note-conceptuelle'))
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Canevas de note conceptuelle introuvable.'
                ], 404);
            }

            // Récupérer la configuration existante ou créer une nouvelle
            $evaluationConfigs = $canevas->evaluation_configs ?? [];

            // Mettre à jour les options de notation
            $evaluationConfigs['options_notation'] = $data['options_notation'];

            // Mettre à jour d'autres configurations si fournies
            if (isset($data['poids_personnalises'])) {
                $evaluationConfigs['poids_personnalises'] = $data['poids_personnalises'];
            }

            if (isset($data['criteres_evaluation'])) {
                $evaluationConfigs['criteres_evaluation'] = $data['criteres_evaluation'];
            }

            // Sauvegarder la configuration
            $canevas->update(['evaluation_configs' => $evaluationConfigs]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Options de notation configurées avec succès.',
                'data' => [
                    'evaluation_configs' => $evaluationConfigs
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer le formulaire de rédaction avec la configuration des options de notation
     */
    public function getOptionsNotationConfig(): JsonResponse
    {
        try {
            // Récupérer le canevas de note conceptuelle avec ses relations
            $canevas = $this->documentRepository->getModel()
                ->where('type', 'formulaire')
                ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-redaction-note-conceptuelle'))
                ->with(['categorie', 'sections.champs', 'champs', 'all_champs'])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Canevas de note conceptuelle introuvable.'
                ], 404);
            }

            $evaluationConfigs = $canevas->evaluation_configs ?? [];

            return response()->json([
                'success' => true,
                'data' => [
                    'formulaire_redaction' => $canevas,
                    'evaluation_configs' => $evaluationConfigs,
                    'options_par_defaut' => StatutEvaluationNoteConceptuelle::options()
                ]
            ]);

        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }


    /**
     * Enregistrer un workflow pour le changement de statut d'une idée de projet
     */
    private function enregistrerWorkflow($ideeProjet, $nouveauStatut, $phase = null, $sousPhase = null)
    {
        Workflow::create([
            'statut' => $nouveauStatut,
            'phase' => $phase ?? $this->getPhaseFromStatut($nouveauStatut),
            'sous_phase' => $sousPhase ?? $this->getSousPhaseFromStatut($nouveauStatut),
            'date' => now(),
            'projetable_id' => $ideeProjet->id,
            'projetable_type' => get_class($ideeProjet),
        ]);
    }

    /**
     * Enregistrer une décision prise concernant une idée de projet
     */
    private function enregistrerDecision($ideeProjet, $valeurDecision, $observations = null, $observateurId = null)
    {
        Decision::create([
            'valeur' => $valeurDecision,
            'date' => now(),
            'observations' => $observations,
            'observateurId' => $observateurId ?? auth()->user()->personne?->id,
            'objet_decision_id' => $ideeProjet->id,
            'objet_decision_type' => get_class($ideeProjet),
        ]);
    }

    /**
     * Obtenir la phase correspondant au statut
     */
    private function getPhaseFromStatut($statut)
    {
        return match ($statut) {
            \App\Enums\StatutIdee::BROUILLON => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::IDEE_DE_PROJET => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::ANALYSE => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::AMC => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::VALIDATION => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::NOTE_CONCEPTUEL => \App\Enums\PhasesIdee::identification,
            default => \App\Enums\PhasesIdee::identification,
        };
    }

    /**
     * Obtenir la sous-phase correspondant au statut
     */
    private function getSousPhaseFromStatut($statut)
    {
        return match ($statut) {
            \App\Enums\StatutIdee::BROUILLON => \App\Enums\SousPhaseIdee::redaction,
            \App\Enums\StatutIdee::IDEE_DE_PROJET => \App\Enums\SousPhaseIdee::redaction,
            \App\Enums\StatutIdee::ANALYSE => \App\Enums\SousPhaseIdee::analyse_idee,
            \App\Enums\StatutIdee::AMC => \App\Enums\SousPhaseIdee::analyse_idee,
            \App\Enums\StatutIdee::VALIDATION => \App\Enums\SousPhaseIdee::analyse_idee,
            \App\Enums\StatutIdee::NOTE_CONCEPTUEL => \App\Enums\SousPhaseIdee::etude_de_profil,
            default => \App\Enums\SousPhaseIdee::redaction,
        };
    }
}