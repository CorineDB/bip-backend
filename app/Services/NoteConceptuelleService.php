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
use App\Http\Resources\CanevasNoteConceptuelleResource;
use App\Http\Resources\ChampResource;
use App\Models\Decision;
use App\Models\Workflow;
use Carbon\Carbon;
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
    ) {
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
            $statutNumeric = match ($statut) {
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
                ->orderBy("created_at", "desc")
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
            ])->whereHas('categorie', function ($query) {
                $query->where('slug', 'canevas-redaction-note-conceptuelle');
            })->orderBy('created_at', 'desc')->first();

            if ($canevasNoteConceptuelle) {
                // Sauvegarder les champs dynamiques basés sur le canevas
                $this->saveDynamicFieldsFromCanevas($noteConceptuelle, $champsData, $canevasNoteConceptuelle);
            }

            $noteConceptuelle->refresh();

            $noteConceptuelle->note_conceptuelle = $noteConceptuelle->champs->map(function ($champ) {
                return [
                    'id' => $champ->id,
                    'label' => $champ->label,
                    'attribut' => $champ->attribut,
                    'valeur' => $champ->pivot->valeur,
                    'commentaire' => $champ->pivot->commentaire,
                    'updated_at' => $champ->pivot->updated_at
                ];
            });

            $noteConceptuelle->save();

            $noteConceptuelle->projet->update([
                'statut' => StatutIdee::VALIDATION_NOTE_AMELIORER,
                'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_NOTE_AMELIORER),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_NOTE_AMELIORER),
                'type_projet' => TypesProjet::simple
            ]);
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
            // Mettre à jour la note existante
            $noteConceptuelle->update($data);

            $message = 'Note conceptuelle mise à jour avec succès.';
            $statusCode = 200;

            // Récupérer le canevas de rédaction de note conceptuelle
            $canevasNoteConceptuelle = $this->documentRepository->getModel()->where([
                'type' => 'formulaire'
            ])->whereHas('categorie', function ($query) {
                $query->where('slug', 'canevas-redaction-note-conceptuelle');
            })->orderBy('created_at', 'desc')->first();

            if ($canevasNoteConceptuelle) {
                // Sauvegarder les champs dynamiques basés sur le canevas
                $this->saveDynamicFieldsFromCanevas($noteConceptuelle, $data, $canevasNoteConceptuelle);
            }

            $noteConceptuelle->refresh();

            $noteConceptuelle->note_conceptuelle = $noteConceptuelle->champs->map(function ($champ) {
                return [
                    'id' => $champ->id,
                    'label' => $champ->label,
                    'attribut' => $champ->attribut,
                    'valeur' => $champ->pivot->valeur,
                    'commentaire' => $champ->pivot->commentaire,
                    'updated_at' => $champ->pivot->updated_at
                ];
            });

            $noteConceptuelle->save();

            if($noteConceptuelle->projet->statut == StatutIdee::NOTE_CONCEPTUEL){
                $noteConceptuelle->projet->update([
                    'statut' => StatutIdee::VALIDATION_NOTE_AMELIORER,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_NOTE_AMELIORER),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_NOTE_AMELIORER),
                    'type_projet' => TypesProjet::simple
                ]);
            }

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
    /*
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
    */

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

            $evaluationEnCours = $noteConceptuelle->evaluationEnCours();

            if (!$evaluationEnCours) {

                $evaluationParent = $noteConceptuelle->evaluationParent();

                // Créer la nouvelle évaluation
                $evaluationData = [
                    'type_evaluation' => "note-conceptuelle",/*
                        'projetable_type' => NoteConceptuelle::class,
                        'projetable_id' => $noteConceptuelle->id, */
                    'evaluateur_id' => auth()->id(),
                    "evaluation" => [],
                    "resultats_evaluation" => [],
                    'date_debut_evaluation' => now(),
                    'statut' => 0, // En cours
                    //'commentaire' => isset($data['raison']) ? $data['raison'] : null,
                    'id_evaluation' => $evaluationParent ? $evaluationParent->id : null // Lien vers le parent
                ];

                $evaluationEnCours = $noteConceptuelle->evaluations()->create($evaluationData);
            } else {
                if ($data["evaluer"]) {
                    $evaluationEnCours->update([
                        'date_fin_evaluation' => now(),
                        'statut' => 1
                    ]);
                }
            }

            // Enregistrer les appréciations pour chaque champ
            if (isset($data['evaluations_champs'])) {

                $syncData = [];

                foreach ($data['evaluations_champs'] as $evaluationChamp) {
                    $syncData[$evaluationChamp['champ_id']] = [
                        'note' => $evaluationChamp['appreciation'],
                        'date_note' => now(),
                        'commentaires' => $evaluationChamp['commentaire'] ?? null,
                    ];
                }

                $evaluationEnCours->champs_evalue()->syncWithoutDetaching($syncData);
            }

            // Enregistrer la raison globale si fournie
            if (isset($data['raison'])) {
                $evaluationEnCours->update(['commentaire' => $data['raison']]);
            }

            $evaluationEnCours->refresh();
            DB::commit();

            $isNewEvaluation = !$noteConceptuelle->evaluationEnCours();
            $message = $data['evaluer'] ?
                'Évaluation finalisée avec succès.' : ($isNewEvaluation ? 'Évaluation créée avec succès.' : 'Appréciations sauvegardées avec succès.');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'evaluation_id' => $evaluationEnCours->id,
                    'statut' => $evaluationEnCours->statut,
                    'appreciations' => $evaluationEnCours->refresh(),
                    'appreciations_count' => count($data['evaluations_champs'] ?? []),
                    'finalise' => $data['evaluer'] ?? false
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
            $totalPoids += 0;

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

            $evaluation = $noteConceptuelle->evaluationEnCours();

            if (!$evaluation) {

                $evaluation = $noteConceptuelle->evaluationTermine();
            }

            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune évaluation trouvée pour cette note conceptuelle.'
                ], 404);
            }

            // Calculer les résultats d'examen
            $resultatsExamen = $this->calculerResultatsExamen($noteConceptuelle, $evaluation);

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => [
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->valider_par,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $evaluation->resultats_evaluation,
                        'statut' => $evaluation->statut,
                        'champs' => collect($noteConceptuelle->note_conceptuelle)->map(function ($champ) use($evaluation) {
                            $champ_evalue = collect($evaluation->champs_evalue)
                            ->firstWhere('attribut', $champ["attribut"]);
                            return [
                                'id' => $champ["id"],
                                'label' => $champ["label"],
                                'attribut' => $champ["attribut"],
                                'valeur' => $champ["valeur"],
                                'appreciation' => $champ_evalue ? $champ_evalue["pivot"]["note"] : null,
                                'commentaire' => $champ_evalue ? $champ_evalue["pivot"]["commentaires"] : null,
                                'date_note' => $champ_evalue ? $champ_evalue["pivot"]["date_note"] : null,
                                'updated_at' => $champ_evalue ? $champ_evalue["pivot"]["updated_at"] : null,
                            ];
                        }),
                    ],
                    'resultats_examen' => $resultatsExamen
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Calculer les résultats d'examen selon vos critères spécifiés
     */
    private function calculerResultatsExamen(NoteConceptuelle $noteConceptuelle, $evaluation): array
    {
        // Récupérer toutes les appréciations
        $champs = collect($noteConceptuelle->note_conceptuelle);
        $champsEvalues = collect($evaluation->champs_evalue);

        // Compter par type d'appréciation
        $nombrePasse = 0;
        $nombreRetour = 0;
        $nombreNonAccepte = 0;
        $nombreNonEvalues = 0;
        $champsObligatoiresNonEvalues = 0;

        foreach ($champs as $champ) {
            $champEvalue = $champsEvalues->firstWhere('attribut', $champ['attribut']);
            $appreciation = $champEvalue ? $champEvalue['pivot']['note'] : null;

            if ($appreciation) {
                switch ($appreciation) {
                    case 'passe':
                        $nombrePasse++;
                        break;
                    case 'retour':
                        $nombreRetour++;
                        break;
                    case 'non_accepte':
                        $nombreNonAccepte++;
                        break;
                }
            } else {
                $nombreNonEvalues++;
                // Si c'est un champ obligatoire et non évalué
                if ($this->isChampObligatoire($champ['attribut'])) {
                    $champsObligatoiresNonEvalues++;
                }
            }
        }

        $totalChamps = $champs->count();

        // Calculer le pourcentage d'évolution
        $pourcentageEvolution = $this->calculerPourcentageEvolution($totalChamps, $nombrePasse, $nombreRetour, $nombreNonAccepte, $nombreNonEvalues);

        // Par défaut, résultat null
        $resultat_global = null;
        $message_resultat = null;
        $raisons = null;
        $recommandations = null;
        $resume = null;

        // Si l'évaluation est terminée (statut = 1), calculer le résultat final
        if ($evaluation->statut == 1) {
            $resultat = $this->determinerResultatExamen([
                'passe' => $nombrePasse,
                'retour' => $nombreRetour,
                'non_accepte' => $nombreNonAccepte,
                'non_evalues' => $nombreNonEvalues,
                'obligatoires_non_evalues' => $champsObligatoiresNonEvalues,
                'total' => $totalChamps
            ]);

            $resultat_global = $resultat['statut'];
            $message_resultat = $resultat['message'];
            $raisons = $resultat['raisons'];
            $recommandations = $resultat['recommandations'];
            $resume = $this->genererResumeExamen($resultat, $nombrePasse, $nombreRetour, $nombreNonAccepte, $pourcentageEvolution);
        }

        return [
            'nombre_passe' => $nombrePasse,
            'nombre_retour' => $nombreRetour,
            'nombre_non_accepte' => $nombreNonAccepte,
            'nombre_non_evalues' => $nombreNonEvalues,
            'total_champs' => $totalChamps,
            'champs_obligatoires_non_evalues' => $champsObligatoiresNonEvalues,
            'resultat_global' => $resultat_global,
            'message_resultat' => $message_resultat,
            'raisons' => $raisons,
            'recommandations' => $recommandations,
            //'pourcentage_evolution' => $pourcentageEvolution,
            'resume' => $resume
        ];
    }

    /**
     * Calculer le pourcentage d'évolution de l'évaluation
     */
    private function calculerPourcentageEvolution(int $totalChamps, int $nombrePasse, int $nombreRetour, int $nombreNonAccepte, int $nombreNonEvalues): array
    {
        if ($totalChamps === 0) {
            return [
                'pourcentage_evaluation' => 0,
                'pourcentage_reussite' => 0,
                'pourcentage_amelioration' => 0,
                'pourcentage_rejet' => 0,
                'progression_globale' => 0
            ];
        }

        // Pourcentage d'évaluation (champs évalués / total)
        $champsEvalues = $nombrePasse + $nombreRetour + $nombreNonAccepte;
        $pourcentageEvaluation = round(($champsEvalues / $totalChamps) * 100, 2);

        // Pourcentage de réussite (passe / total évalué)
        $pourcentageReussite = $champsEvalues > 0 ? round(($nombrePasse / $champsEvalues) * 100, 2) : 0;

        // Pourcentage d'amélioration (retour / total évalué)
        $pourcentageAmelioration = $champsEvalues > 0 ? round(($nombreRetour / $champsEvalues) * 100, 2) : 0;

        // Pourcentage de rejet (non_accepte / total évalué)
        $pourcentageRejet = $champsEvalues > 0 ? round(($nombreNonAccepte / $champsEvalues) * 100, 2) : 0;

        // Progression globale (pondérée : passe = 1, retour = 0.5, non_accepte = 0)
        $scoreGlobal = ($nombrePasse * 1) + ($nombreRetour * 0.5) + ($nombreNonAccepte * 0);
        $progressionGlobale = $totalChamps > 0 ? round(($scoreGlobal / $totalChamps) * 100, 2) : 0;

        return [
            'pourcentage_evaluation' => $pourcentageEvaluation,      // % de champs évalués
            'pourcentage_reussite' => $pourcentageReussite,          // % de réussite sur les évalués
            'pourcentage_amelioration' => $pourcentageAmelioration,  // % nécessitant amélioration
            'pourcentage_rejet' => $pourcentageRejet,                // % rejetés
            'progression_globale' => $progressionGlobale,            // Score global pondéré
            'statut_progression' => $this->determinerStatutProgression($progressionGlobale, $pourcentageEvaluation)
        ];
    }

    /**
     * Déterminer le statut de progression basé sur les pourcentages
     */
    private function determinerStatutProgression(float $progressionGlobale, float $pourcentageEvaluation): string
    {
        if ($pourcentageEvaluation < 50) {
            return 'evaluation_incomplete';  // Moins de 50% évalué
        } elseif ($progressionGlobale >= 90) {
            return 'excellent';              // 90%+ de progression
        } elseif ($progressionGlobale >= 75) {
            return 'tres_bien';              // 75-89% de progression
        } elseif ($progressionGlobale >= 60) {
            return 'bien';                   // 60-74% de progression
        } elseif ($progressionGlobale >= 40) {
            return 'passable';               // 40-59% de progression
        } else {
            return 'insuffisant';            // Moins de 40% de progression
        }
    }

    /**
     * Vérifier si un champ est obligatoire selon le canevas
     */
    private function isChampObligatoire(string $attribut): bool
    {
        try {
            $canevas = $this->documentRepository->getModel()
                ->where('type', 'formulaire')
                ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-redaction-note-conceptuelle'))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($canevas) {
                $champ = $canevas->all_champs->firstWhere('attribut', $attribut);
                return $champ ? ($champ->is_required ?? false) : false;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Déterminer le résultat d'examen selon les règles métier
     */
    private function determinerResultatExamen(array $compteurs): array
    {
        // Règle 1: Si des questions obligatoires n'ont pas été complétées
        if ($compteurs['obligatoires_non_evalues'] > 0) {
            return [
                'statut' => 'non_accepte',
                'message' => 'Non accepté',
                'raisons' => ["Des questions obligatoires n'ont pas été complétées ({$compteurs['obligatoires_non_evalues']} champ(s))"],
                'recommandations' => ["Compléter tous les champs obligatoires avant soumission"]
            ];
        }

        // Règle 2: Si une réponse a été évaluée comme "Non accepté"
        if ($compteurs['non_accepte'] > 0) {
            return [
                'statut' => 'non_accepte',
                'message' => 'Non accepté',
                'raisons' => ["{$compteurs['non_accepte']} réponse(s) évaluée(s) comme \"Non accepté\""],
                'recommandations' => ["Revoir complètement les sections marquées comme \"Non accepté\""]
            ];
        }

        // Règle 3: Si 10 ou plus des réponses ont été évaluées comme "Retour"
        if ($compteurs['retour'] >= 10) {
            return [
                'statut' => 'non_accepte',
                'message' => 'Non accepté',
                'raisons' => ["{$compteurs['retour']} réponses évaluées comme \"Retour\" (seuil maximum: 10)"],
                'recommandations' => ["Réviser en profondeur la note conceptuelle"]
            ];
        }

        // Si toutes les questions ont reçu "Passe"
        if ($compteurs['passe'] === $compteurs['total'] && $compteurs['non_evalues'] === 0) {
            return [
                'statut' => 'passe',
                'message' => 'La présélection a été un succès (passes reçues dans toutes les questions)',
                'raisons' => [],
                'recommandations' => []
            ];
        }

        // Sinon: Retour pour travail supplémentaire
        $recommandations = [];
        if ($compteurs['retour'] > 0) {
            $recommandations[] = "Améliorer les {$compteurs['retour']} point(s) marqué(s) comme \"Retour\"";
        }
        if ($compteurs['non_evalues'] > 0) {
            $recommandations[] = "Attendre l'évaluation des {$compteurs['non_evalues']} champ(s) restant(s)";
        }

        return [
            'statut' => 'retour',
            'message' => 'Retour pour un travail supplémentaire (Contient des « Retours » mais pas suffisamment pour qu\'il ne soit pas accepté)',
            'raisons' => [],
            'recommandations' => $recommandations
        ];
    }

    /**
     * Générer le résumé d'examen formaté
     */
    private function genererResumeExamen(array $resultat, int $nombrePasse, int $nombreRetour, int $nombreNonAccepte, array $pourcentageEvolution): string
    {
        $resume = "Résultats de l'examen\n\n";
        $resume .= "Nombre de Passer : {$nombrePasse} ({$pourcentageEvolution['pourcentage_reussite']}%)\n";
        $resume .= "Nombre de Retour : {$nombreRetour} ({$pourcentageEvolution['pourcentage_amelioration']}%)\n";
        $resume .= "Nombre de Non accepté : {$nombreNonAccepte} ({$pourcentageEvolution['pourcentage_rejet']}%)\n\n";

        $resume .= "Statistiques d'évolution :\n";
        $resume .= "• Progression d'évaluation : {$pourcentageEvolution['pourcentage_evaluation']}%\n";
        $resume .= "• Progression globale : {$pourcentageEvolution['progression_globale']}%\n";
        $resume .= "• Statut : " . ucfirst(str_replace('_', ' ', $pourcentageEvolution['statut_progression'])) . "\n\n";

        $resume .= "Le résultat de l'examen est donc le suivant :\n\n";

        switch ($resultat['statut']) {
            case 'passe':
                $resume .= "(✓) La présélection a été un succès (passes reçues dans toutes les questions)\n\n";
                $resume .= "( ) Retour pour un travail supplémentaire\n\n";
                $resume .= "( ) Non accepté\n";
                break;

            case 'retour':
                $resume .= "( ) La présélection a été un succès (passes reçues dans toutes les questions)\n\n";
                $resume .= "(✓) Retour pour un travail supplémentaire (Non, « Non accepté » contient des « Retours » mais pas suffisamment pour qu'il ne soit pas accepté)\n";
                $resume .= "Raisons et recommandations d'amélioration :\n";
                foreach ($resultat['recommandations'] as $recommandation) {
                    $resume .= "- {$recommandation}\n";
                }
                $resume .= "\n( ) Non accepté\n";
                break;

            case 'non_accepte':
                $resume .= "( ) La présélection a été un succès (passes reçues dans toutes les questions)\n\n";
                $resume .= "( ) Retour pour un travail supplémentaire\n\n";
                $resume .= "(✓) Non accepté\n";
                $resume .= "Seules raisons possibles de rejet :\n";
                $resume .= "1. Si des questions n'ont pas été complétées\n";
                $resume .= "2. Si une réponse à une question a été évaluée comme « Non accepté » ou\n";
                $resume .= "3. Si 10 ou plus des réponses ont été évaluées comme « Retour »\n";
                $resume .= "Raison(s) :\n";
                foreach ($resultat['raisons'] as $raison) {
                    $resume .= "- {$raison}\n";
                }
                break;
        }

        return $resume;
    }

    /**
     * Confirmer le résultat de l'évaluation avec commentaires et finalisation
     */
    public function confirmerResultat(int $evaluationId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Récupérer l'évaluation
            $evaluation = $this->evaluationRepository->findOrFail($evaluationId);

            if ($evaluation->statut != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'évaluation doit être terminée avant de pouvoir confirmer le résultat.'
                ], 422);
            }

            // Récupérer la note conceptuelle
            $noteConceptuelle = $this->repository->find($evaluation->projetable_id);
            if (!$noteConceptuelle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note conceptuelle non trouvée.'
                ], 404);
            }

            // Calculer les résultats d'examen finaux
            $resultatsExamen = $this->calculerResultatsExamen($noteConceptuelle, $evaluation);

            // Préparer l'évaluation complète pour enregistrement
            $evaluationComplete = [
                'champs_evalues' => collect($noteConceptuelle->note_conceptuelle)->map(function ($champ) use ($evaluation) {
                    $champEvalue = collect($evaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                    return [
                        'champ_id' => $champ['id'],
                        'label' => $champ['label'],
                        'attribut' => $champ['attribut'],
                        'valeur' => $champ['valeur'],
                        'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                        'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                        'date_evaluation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                    ];
                })->toArray(),
                'statistiques' => $resultatsExamen,
                'commentaire_confirmation' => $data['commentaire_confirmation'] ?? null,
                'date_confirmation' => now(),
                'confirme_par' => auth()->id()
            ];

            // Mettre à jour l'évaluation avec les données complètes
            $evaluation->update([
                'resultats_evaluation' => $resultatsExamen,
                'evaluation' => $evaluationComplete,
                'valider_par' => auth()->id(),
                'valider_le' => now(),
                'commentaire' => ($evaluation->commentaire ?? '') . "\n\nCommentaire de confirmation: " . ($data['commentaire_confirmation'] ?? '')
            ]);

            // Enregistrer la décision et la raison dans la note conceptuelle
            $noteConceptuelleUpdate = [
                'decision' => $resultatsExamen['resultat_global'],
                'raison_decision' => $data['commentaire_confirmation'] ?? $resultatsExamen['message_resultat'],
                'valider_par' => auth()->id(),
                'valider_le' => now()
            ];

            $noteConceptuelleData = [];

            // Mettre à jour le statut de la note selon le résultat
            switch ($resultatsExamen['resultat_global']) {
                case 'passe':
                    $noteConceptuelleUpdate['statut'] = 1; // Acceptée
                    $noteConceptuelleData = [
                        'statut' => StatutIdee::VALIDATION_PROFIL,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_PROFIL),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_PROFIL),
                    ];
                    break;
                case 'retour':
                    $noteConceptuelleUpdate['statut'] = 0; // En révision
                    $noteConceptuelleData = [
                        'statut' => StatutIdee::R_VALIDATION_NOTE_AMELIORER,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::R_VALIDATION_NOTE_AMELIORER),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::R_VALIDATION_NOTE_AMELIORER),
                    ];
                    break;
                case 'non_accepte':
                    $noteConceptuelleUpdate['statut'] = -1; // Rejetée
                    $noteConceptuelleData = [
                        'statut' => StatutIdee::NOTE_CONCEPTUEL,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                    ];
                    break;
            }

            $noteConceptuelle->update($noteConceptuelleUpdate);

            $noteConceptuelle->projet->update($noteConceptuelleData);

            // Enregistrer dans l'historique des décisions si nécessaire
            $this->enregistrerDecisionEvaluation($noteConceptuelle, $resultatsExamen, $data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Résultat de l\'évaluation confirmé avec succès.',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'note_conceptuelle_id' => $noteConceptuelle->id,
                    'resultat_final' => $resultatsExamen['resultat_global'],
                    'decision' => $noteConceptuelle->decision,
                    'nouveau_statut_note' => $noteConceptuelle->statut,
                    'valider_par' => auth()->id(),
                    'valider_le' => now()->format('d/m/Y H:i:s'),
                    'statistiques' => [
                        'nombre_passe' => $resultatsExamen['nombre_passe'],
                        'nombre_retour' => $resultatsExamen['nombre_retour'],
                        'nombre_non_accepte' => $resultatsExamen['nombre_non_accepte'],
                        //'progression_globale' => $resultatsExamen['pourcentage_evolution']['progression_globale']
                    ]
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Confirmer le résultat de l'évaluation par ID de note conceptuelle
     */
    public function confirmerResultatParNote(int $noteConceptuelleId, array $data): JsonResponse
    {
        try {
            // Récupérer la note conceptuelle
            $noteConceptuelle = $this->repository->findOrFail($noteConceptuelleId);

            // Trouver l'évaluation terminée pour cette note
            $evaluation = $noteConceptuelle->evaluations()
                ->where('type_evaluation', 'note-conceptuelle')
                ->where('statut', 1) // Terminée
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune évaluation terminée trouvée pour cette note conceptuelle.'
                ], 404);
            }

            // Utiliser la méthode existante avec l'ID d'évaluation
            return $this->confirmerResultat($evaluation->id, $data);

        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Enregistrer la décision d'évaluation dans l'historique
     */
    private function enregistrerDecisionEvaluation(NoteConceptuelle $noteConceptuelle, array $resultatsExamen, array $data): void
    {
        try {
            // Créer un enregistrement de décision dans la table Decision
            Decision::create([
                'valeur' => "Évaluation de la note conceptuelle - Résultat: " . ucfirst($resultatsExamen['resultat_global']),
                'date' => now(),
                'observations' => $data['commentaire_confirmation'] ?? $resultatsExamen['message_resultat'],
                'observateurId' => auth()->id(),
                'objet_decision_id' => $noteConceptuelle->id,
                'objet_decision_type' => NoteConceptuelle::class,
            ]);
        } catch (Exception $e) {
            // Log l'erreur mais ne pas faire échouer la transaction principale
            \Log::warning('Erreur lors de l\'enregistrement de la décision d\'évaluation: ' . $e->getMessage());
        }
    }

    /**
     * Validation du projet à l'étape Etude de profil (SFD-009)
     */
    public function validerEtudeDeProfil(int $projetId, array $data): JsonResponse
    {
        try {
            // Vérifier les autorisations (Comité de validation Ministériel)
            if (!in_array(auth()->user()->type, ['comite_ministeriel', 'dpaf', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette validation.'
                ], 403);
            }

            DB::beginTransaction();

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::VALIDATION_PROFIL->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de validation d\'étude de profil.'
                ], 422);
            }

            // Récupérer la note conceptuelle du projet
            $noteConceptuelle = $this->repository->getModel()
                ->where('projetId', $projetId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$noteConceptuelle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune note conceptuelle trouvée pour ce projet.'
                ], 404);
            }

            // Récupérer le résultat de l'évaluation pour déterminer les boutons actifs
            $resultatsEvaluation = $this->getResultatsEvaluationPourValidation($noteConceptuelle);

            // Valider que l'action choisie est autorisée selon le résultat d'évaluation
            $actionsAutorisees = $this->getActionsAutoriseesSelonEvaluation($resultatsEvaluation);

            if (!in_array($data['decision'], $actionsAutorisees)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette action n\'est pas autorisée selon le résultat de l\'évaluation.',
                    'actions_autorisees' => $actionsAutorisees,
                    'resultat_evaluation' => $resultatsEvaluation['resultat_global'] ?? 'non_defini'
                ], 422);
            }

            // Traiter la décision selon le cas d'utilisation
            $nouveauStatut = $this->traiterDecisionValidation($projet, $data['decision'], $data);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, $nouveauStatut);
            $this->enregistrerDecision(
                $projet,
                "Validation de l'étude de profil - " . ucfirst(str_replace('_', ' ', $data['decision'])),
                $data['commentaire'] ?? '',
                auth()->user()->id
            );

            // Envoyer des notifications si nécessaire
            if ($data['decision'] !== 'sauvegarder') {
                $this->envoyerNotificationValidation($projet, $data['decision'], $data['commentaire'] ?? '');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $this->getMessageSuccesValidation($data['decision']),
                'data' => [
                    'projet_id' => $projet->id,
                    'ancien_statut' => StatutIdee::VALIDATION_PROFIL->value,
                    'nouveau_statut' => $nouveauStatut->value,
                    'decision' => $data['decision'],
                    'commentaire' => $data['commentaire'] ?? '',
                    'valider_par' => auth()->id(),
                    'valider_le' => now()->format('d/m/Y H:i:s'),
                    'actions_effectuees' => $this->getActionsEffectuees($data['decision'])
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les résultats d'évaluation pour la validation
     */
    private function getResultatsEvaluationPourValidation($noteConceptuelle): array
    {
        // Trouver l'évaluation confirmée la plus récente
        $evaluation = $noteConceptuelle->evaluations()
            ->where('type_evaluation', 'note-conceptuelle')
            ->where('statut', 1) // Terminée
            ->whereNotNull('valider_par') // Confirmée
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$evaluation || !$evaluation->resultats_evaluation) {
            return [
                'resultat_global' => 'non_defini',
                'evaluation_existe' => false
            ];
        }

        return array_merge($evaluation->resultats_evaluation, [
            'evaluation_existe' => true,
            'evaluation_id' => $evaluation->id
        ]);
    }

    /**
     * Déterminer les actions autorisées selon le résultat d'évaluation
     */
    private function getActionsAutoriseesSelonEvaluation(array $resultatsEvaluation): array
    {
        $resultatGlobal = $resultatsEvaluation['resultat_global'] ?? 'non_defini';

        switch ($resultatGlobal) {
            case 'passe':
                // La présélection a été un succès
                return ['projet_a_maturite', 'faire_etude_prefaisabilite', 'sauvegarder'];

            case 'retour':
            case 'non_accepte':
                // Retour pour travail supplémentaire ou Non accepté
                return ['abandonner_projet', 'reviser_note_conceptuelle', 'sauvegarder'];

            default:
                // Évaluation non définie - toutes les actions sont possibles
                return ['projet_a_maturite', 'faire_etude_prefaisabilite', 'reviser_note_conceptuelle', 'abandonner_projet', 'sauvegarder'];
        }
    }

    /**
     * Traiter la décision de validation selon le cas d'utilisation
     */
    private function traiterDecisionValidation($projet, string $decision, array $data): \App\Enums\StatutIdee
    {
        switch ($decision) {
            case 'projet_a_maturite':
                $projet->update([
                    'statut' => StatutIdee::PRET,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::PRET),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::PRET),
                    'type_projet' => TypesProjet::simple
                ]);
                return StatutIdee::PRET;

            case 'faire_etude_prefaisabilite':
                $projet->update([
                    'statut' => StatutIdee::TDR_PREFAISABILITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                    'type_projet' => TypesProjet::complexe1
                ]);
                return StatutIdee::TDR_PREFAISABILITE;

            case 'reviser_note_conceptuelle':
                $projet->update([
                    'statut' => StatutIdee::NOTE_CONCEPTUEL,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL)
                ]);
                return StatutIdee::NOTE_CONCEPTUEL;

            case 'abandonner_projet':
                $projet->update([
                    'statut' => StatutIdee::ABANDON,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::ABANDON),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::ABANDON)
                ]);
                return StatutIdee::ABANDON;

            case 'sauvegarder':
                // Pas de changement de statut, juste sauvegarde du commentaire
                return StatutIdee::VALIDATION_PROFIL;

            default:
                throw new Exception("Décision non reconnue: {$decision}");
        }
    }

    /**
     * Envoyer une notification selon la décision
     */
    private function envoyerNotificationValidation($projet, string $decision, string $commentaire): void
    {
        try {
            $typeNotification = match($decision) {
                'projet_a_maturite' => 'projet_pret',
                'faire_etude_prefaisabilite' => 'etude_prefaisabilite_requise',
                'reviser_note_conceptuelle' => 'revision_note_requise',
                'abandonner_projet' => 'projet_abandonne',
                default => null
            };

            if ($typeNotification) {
                // Ici vous pouvez implémenter l'envoi de notification
                // Par exemple, en utilisant un service de notification
                \Log::info("Notification à envoyer: {$typeNotification} pour le projet {$projet->id}");
            }
        } catch (Exception $e) {
            \Log::warning("Erreur lors de l'envoi de notification: " . $e->getMessage());
        }
    }

    /**
     * Obtenir le message de succès selon la décision
     */
    private function getMessageSuccesValidation(string $decision): string
    {
        return match($decision) {
            'projet_a_maturite' => 'Projet validé comme prêt pour la suite.',
            'faire_etude_prefaisabilite' => 'Projet orienté vers une étude de pré-faisabilité.',
            'reviser_note_conceptuelle' => 'Projet renvoyé pour révision de la note conceptuelle.',
            'abandonner_projet' => 'Projet abandonné.',
            'sauvegarder' => 'Commentaires sauvegardés.',
            default => 'Validation effectuée avec succès.'
        };
    }

    /**
     * Obtenir les actions effectuées selon la décision
     */
    private function getActionsEffectuees(string $decision): array
    {
        return match($decision) {
            'projet_a_maturite' => [
                'statut_change' => 'Statut changé vers "Prêt"',
                'type_projet' => 'Type défini comme "Simple"',
                'notification' => 'Notification envoyée'
            ],
            'faire_etude_prefaisabilite' => [
                'statut_change' => 'Statut changé vers "TDR Pré-faisabilité"',
                'type_projet' => 'Type défini comme "Complexe1"',
                'notification' => 'Notification envoyée'
            ],
            'reviser_note_conceptuelle' => [
                'statut_change' => 'Statut changé vers "Note Conceptuelle"',
                'notification' => 'Notification envoyée'
            ],
            'abandonner_projet' => [
                'statut_change' => 'Statut changé vers "Abandon"',
                'notification' => 'Notification d\'abandon envoyée'
            ],
            'sauvegarder' => [
                'commentaire_sauvegarde' => 'Commentaires sauvegardés sans changement de statut'
            ],
            default => []
        };
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

            if (isset($data['options_notation'])) {

                // Mettre à jour les options de notation
                $evaluationConfigs['options_notation'] = $data['options_notation'];

                // Sauvegarder la configuration
                $canevas->update(['evaluation_configs' => $evaluationConfigs]);

                DB::commit();
            }

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
                ->with(['categorie'])
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
                    'grille_evaluation' => new CanevasNoteConceptuelleResource($canevas),
                    'evaluation_configs' => $evaluationConfigs
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
            'observateurId' => $observateurId ?? auth()->user()->id,
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
            \App\Enums\StatutIdee::VALIDATION_PROFIL => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::VALIDATION_NOTE_AMELIORER => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER => \App\Enums\PhasesIdee::identification,
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
            \App\Enums\StatutIdee::VALIDATION_PROFIL => \App\Enums\SousPhaseIdee::etude_de_profil,
            \App\Enums\StatutIdee::VALIDATION_NOTE_AMELIORER => \App\Enums\SousPhaseIdee::etude_de_profil,
            \App\Enums\StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER => \App\Enums\SousPhaseIdee::etude_de_profil,
            default => \App\Enums\SousPhaseIdee::redaction,
        };
    }
}
