<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\NoteConceptuelleResource;
use App\Models\NoteConceptuelle;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\NoteConceptuelleRepositoryInterface;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Services\Contracts\NoteConceptuelleServiceInterface;
use App\Repositories\Contracts\FichierRepositoryInterface;
use App\Enums\StatutEvaluationNoteConceptuelle;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use App\Http\Resources\CanevasAppreciationTdrResource;
use App\Http\Resources\CanevasNoteConceptuelleResource;
use App\Http\Resources\ChampResource;
use App\Http\Resources\EvaluationResource;
use App\Http\Resources\projets\ProjetsResource;
use App\Http\Resources\RapportResource;
use App\Http\Resources\UserResource;
use App\Models\Decision;
use App\Models\Dgpd;
use App\Models\Dossier;
use App\Models\Dpaf;
use App\Models\Fichier;
use App\Models\Projet;
use App\Models\Rapport;
use App\Models\Workflow;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class NoteConceptuelleService extends BaseService implements NoteConceptuelleServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected DocumentRepositoryInterface $documentRepository;
    protected ProjetRepositoryInterface $projetRepository;
    protected EvaluationRepositoryInterface $evaluationRepository;
    protected FichierRepositoryInterface $fichierRepository;

    public function __construct(
        NoteConceptuelleRepositoryInterface $repository,
        DocumentRepositoryInterface $documentRepository,
        ProjetRepositoryInterface $projetRepository,
        EvaluationRepositoryInterface $evaluationRepository,
        FichierRepositoryInterface $fichierRepository
    ) {
        parent::__construct($repository);

        $this->documentRepository = $documentRepository;
        $this->projetRepository = $projetRepository;
        $this->evaluationRepository = $evaluationRepository;
        $this->fichierRepository = $fichierRepository;
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

            if (!auth()->user()->hasPermissionTo('creer-une-note-conceptuelle') && !auth()->user()->hasPermissionTo('rediger-une-note-conceptuelle') && auth()->user()->type !== 'dpaf' && auth()->user()->profilable_type !== Dpaf::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            // Extraire les données spécifiques au payload
            $champsData = $data['champs'] ?? [];
            $documentsData = $data['documents'] ?? [];
            $estSoumise = $data['est_soumise'] ?? false;
            $projetId = $data['projetId'] ?? null;

            if (!$projetId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID du projet requis.'
                ], 422);
            }

            // Vérifier que le projet existe
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            /* if ($projet->statut->value != StatutIdee::NOTE_CONCEPTUEL->value && $projet->statut->value != StatutIdee::R_VALIDATION_NOTE_AMELIORER->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de redaction de la note conceptuelle.'
                ], 403);
            } */

            // Déterminer le statut selon est_soumise
            $statut = $estSoumise ? 'soumise' : 'brouillon';

            // Préparer les données de la note conceptuelle
            $intitule = 'Note conceptuelle';

            // Convertir le statut en numérique selon l'enum de la table
            $statutNumeric = match ($statut) {
                'soumise'   => 1,
                'brouillon' => 0,
                default     => 0 // brouillon
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
                ->where('statut', '<>', 1)
                ->orderBy("created_at", "desc")
                ->first();

            if ($noteConceptuelle) {

                if (auth()->user()->profilable?->ministere?->id !== $noteConceptuelle->projet->ministere->id) {
                    throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
                }

                if (auth()->user()->id !== $noteConceptuelle->rediger_par) {
                    throw new Exception("Vous n'avez pas les droits d'acces de modifier cette note conceptuelle", 403);
                }

                // Mettre à jour la note existante
                $noteConceptuelle->fill($noteData);
                $noteConceptuelle->save();
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

            // Gérer les documents/fichiers avec FichierRepository
            if (!empty($documentsData)) {
                $noteFiles = [];

                if (isset($documentsData["note_conceptuelle"])) {
                    $noteFiles["note_conceptuelle"] = $documentsData["note_conceptuelle"];
                }

                if (isset($documentsData["analyse_pre_risque_facteurs_reussite"])) {
                    $noteFiles["analyse_pre_risque_facteurs_reussite"] = $documentsData["analyse_pre_risque_facteurs_reussite"];
                }

                $this->handleDocumentsWithFichierRepository($noteConceptuelle, $noteFiles);
            }

            $noteConceptuelle->note_conceptuelle = $noteConceptuelle->champs->map(function ($champ) {
                return [
                    'id' => $champ->id,
                    'label' => $champ->label,
                    'attribut' => $champ->attribut,
                    'ordre_affichage' => $champ['ordre_affichage'],
                    'type_champ' => $champ['type_champ'],
                    'valeur' => $champ->pivot->valeur,
                    'commentaire' => $champ->pivot->commentaire,
                    'updated_at' => $champ->pivot->updated_at
                ];
            });

            $noteConceptuelle->canevas_redaction_note_conceptuelle = (new CanevasNoteConceptuelleResource($this->documentRepository->getCanevasRedactionNoteConceptuelle()))->toArray(request());

            $noteConceptuelle->save();

            // Récupérer le dernier rapport de préfaisabilité s'il existe
            $rapportExistant = $projet->rapportFaisabilitePreliminaire()->first();

            // Préparer les données du rapport
            $rapportData = [
                'projet_id' => $projet->id,
                'type' => 'faisabilite-preliminaire',
                'statut' => $estSoumise ? 'brouillon' : 'soumis',
                'intitule' => 'Rapport de faisabilité préliminaire',
                'checklist_suivi' => [],
                'info_cabinet_etude' => [],
                'recommandation' => null,
                'soumis_par_id' => auth()->id(),
                'date_soumission' =>  $estSoumise ? now() : null,
            ];

            // Créer ou mettre à jour le rapport
            if ($rapportExistant && $rapportExistant->statut === 'brouillon') {
                // Mettre à jour le rapport existant s'il est en brouillon
                $rapport = $rapportExistant;
                $rapport->fill($rapportData);
                $rapport->save();
                $message = $estSoumise ? 'Rapport sauvegardé en brouillon.' : 'Rapport soumis avec succès.';
            } elseif ($rapportExistant && $rapportExistant->statut === 'soumis' && !$estSoumise) {
                // Si un rapport soumis existe déjà et qu'on soumet à nouveau, créer une nouvelle version
                $rapportData['parent_id'] = $rapportExistant->id;
                $rapport = Rapport::create($rapportData);
                $message = 'Nouvelle version du rapport soumise avec succès.';
            } else {
                // Créer un nouveau rapport (première version)
                $rapport = Rapport::create($rapportData);
                $message = $estSoumise ? 'Rapport sauvegardé en brouillon.' : 'Rapport soumis avec succès.';
            }

            if (isset($documentsData["rapport_faisabilite_preliminaire"])) {
                $this->gererFichierRapportFaisabilite($rapport, $documentsData['rapport_faisabilite_preliminaire']);
            }

            if (isset($documentsData["tdr_faisabilite_preliminaire"])) {
                $this->gererFichierRapportFaisabilite($rapport, $documentsData['tdr_faisabilite_preliminaire']);
            }

            if (isset($documentsData["check_suivi_rapport"])) {
                $this->gererFichierRapportFaisabilite($rapport, $documentsData['check_suivi_rapport']);
            }

            if ($estSoumise) {
                // Gérer l'analyse financière et calculer la VAN et le TRI
                if (isset($data['analyse_financiere'])) {
                    $updateData = [];
                    $analyseFinanciere = $data['analyse_financiere'];

                    $requiredFields = ['duree_vie', 'investissement_initial', 'flux_tresorerie', 'taux_actualisation'];

                    foreach ($requiredFields as $field) {
                        // validation de présence de $analyseFinanciere[$field]
                        if (!isset($analyseFinanciere[$field]) && !empty($analyseFinanciere[$field])) {
                            throw ValidationException::withMessages([
                                "analyse_financiere.$field" => "Le champ $field est obligatoire lorsque le projet est financé. " . $analyseFinanciere[$field]
                            ]);
                        }
                        // validations supplémentaires pour les champs spécifiques
                        // Il faut savoir que les donnees sont soumis dans un formdata donc tout est string

                        if ($field === 'duree_vie') {

                            $value = $analyseFinanciere[$field];

                            // Vérifie que c'est bien un nombre ET un entier positif
                            if (!ctype_digit((string)$value) || (int)$value <= 0) {
                                throw ValidationException::withMessages([
                                    "analyse_financiere.$field" => "Le champ $field doit être un nombre entier positif (sans virgule)."
                                ]);
                            }

                            // Optionnel : convertir proprement en entier
                            $analyseFinanciere[$field] = (int)$value;
                        }

                        // Ajouter d'autres validations spécifiques si nécessaire
                        if (in_array($field, ['investissement_initial', 'taux_actualisation'])) {
                            if (!is_numeric($analyseFinanciere[$field])) {
                                throw ValidationException::withMessages([
                                    "analyse_financiere.$field" => "Le champ $field doit être une date valide au format AAAA-MM-JJ."
                                ]);
                            }

                            // Optionnel : forcer la conversion en float si tu veux l'utiliser ensuite
                            $analyseFinanciere[$field] = (float) $analyseFinanciere[$field];
                        }
                    }

                    // Préparer les données pour le fill() et la mise à jour
                    $financialData = [
                        'duree_vie' => $analyseFinanciere['duree_vie'] ?? $projet->duree_vie,
                        'investissement_initial' => $analyseFinanciere['investissement_initial'] ?? $projet->investissement_initial,
                        'flux_tresorerie' => $analyseFinanciere['flux_tresorerie'] ?? $projet->flux_tresorerie,
                        'taux_actualisation' => $analyseFinanciere['taux_actualisation'] ?? $projet->taux_actualisation,
                    ];

                    // Mettre à jour le modèle en mémoire avec les nouvelles données financières
                    $rapport->fill($financialData);

                    // Calculer la VAN et le TRI à partir des données mises à jour
                    $van = $rapport->calculerVAN();
                    $rapport->van = $van;
                    $tri = $rapport->calculerTRI();

                    // Ajouter toutes les données financières et les résultats au tableau de mise à jour
                    $updateData = array_merge($updateData, $financialData);

                    if ($van !== null) {
                        $updateData['van'] = $van;
                    }
                    if ($tri !== null) {
                        $updateData['tri'] = $tri;
                    }

                    $rapport->update($updateData);
                }
            }

            if ($projet->statut->value == StatutIdee::NOTE_CONCEPTUEL->value && $noteConceptuelle->statut == 1 && $estSoumise) {

                $noteConceptuelle->projet->fill([
                    'statut' => StatutIdee::EVALUATION_NOTE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::EVALUATION_NOTE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::EVALUATION_NOTE),
                    'type_projet' => TypesProjet::simple
                ]);
            }

            $noteConceptuelle->projet->save();
            $noteConceptuelle->projet->refresh();

            DB::commit();

            return (new $this->resourceClass($noteConceptuelle->load("fichiers")))
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

            if (!auth()->user()->hasPermissionTo('modifier-une-note-conceptuelle') && !auth()->user()->hasPermissionTo('rediger-une-note-conceptuelle') && auth()->user()->type !== 'dpaf' && auth()->user()->profilable_type !== Dpaf::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer la note conceptuelle pour obtenir le projetId
            $noteConceptuelle = $this->repository->findOrFail($id);

            if (auth()->user()->profilable?->ministere?->id !== $noteConceptuelle->projet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier que le projet est au bon statut
            if ($noteConceptuelle->projet->statut->value != StatutIdee::NOTE_CONCEPTUEL->value && $noteConceptuelle->projet->statut->value != StatutIdee::R_VALIDATION_NOTE_AMELIORER->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de redaction de la note conceptuelle.'
                ], 403);
            }

            if ($noteConceptuelle->statut) {
                throw new Exception("Vous ne pouvez pas effectuer cette action.", 403);
            }

            if (auth()->user()->id !== $noteConceptuelle->rediger_par) {
                throw new Exception("Vous n'avez pas les droits d'acces de modifier cette note conceptuelle", 403);
            }

            $estSoumise = $data['est_soumise'] ?? false;

            // Déterminer le statut selon est_soumise
            $statut = $estSoumise ? 'soumise' : 'brouillon';

            // Convertir le statut en numérique selon l'enum de la table
            $statutNumeric = match ($statut) {
                'soumise'   => 1,
                'brouillon' => 0,
                default     => 0 // brouillon
            };

            $noteData = [
                'statut' => $statutNumeric,
            ];

            $documentsData = $data['documents'] ?? [];

            // Mettre à jour la note existante
            $noteConceptuelle->update(array_merge($data, $noteData));

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

            // Gérer les documents/fichiers avec FichierRepository
            if (!empty($documentsData)) {
                $this->handleDocumentsWithFichierRepository($noteConceptuelle, $documentsData);
            }

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

            if ($noteConceptuelle->projet->statut->value == StatutIdee::NOTE_CONCEPTUEL->value && $noteConceptuelle->statut == 1 && $estSoumise) {

                $noteConceptuelle->projet->fill([
                    'statut' => StatutIdee::EVALUATION_NOTE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::EVALUATION_NOTE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::EVALUATION_NOTE),
                    'type_projet' => TypesProjet::simple
                ]);
            }
            /*

            if ($noteConceptuelle->projet->statut == StatutIdee::NOTE_CONCEPTUEL) {
                $noteConceptuelle->projet->update([
                    'statut' => StatutIdee::VALIDATION_NOTE_AMELIORER,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_NOTE_AMELIORER),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_NOTE_AMELIORER),
                    'type_projet' => TypesProjet::simple
                ]);
            } */

            return (new $this->resourceClass($noteConceptuelle))
                ->additional(['message' => $message])
                ->response()
                ->setStatusCode($statusCode);
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

            if (!auth()->user()->hasPermissionTo('valider-l-etude-de-profil') && (auth()->user()->profilable?->ministere?->id !== $projet->ministere->id)) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

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

            switch ($data["decision"]) {/*
                case 'a_maturite':
                    $projet->update([
                        'date_fin_etude' => now(),
                        'statut' => StatutIdee::PRET,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::PRET),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::PRET),
                        'type_projet' => TypesProjet::simple
                    ]);
                    break; */
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
                        'date_fin_etude' => now(),
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

    /**
     * Récupérer une note conceptuelle d'un projet
     */
    public function getForProject($projetId): JsonResponse
    {
        try {

            // Vérifier que le projet existe
            $projet = $this->projetRepository->findOrFail($projetId);

            $noteConceptuelle = $this->repository->getModel()
                ->where('projetId', $projet->id)
                ->orderBy("created_at", "desc")
                ->first();

            if (!$noteConceptuelle) {
                return response()->json([
                    'success' => true,
                    'message' => 'Note conceptuelle non trouvée pour ce projet.'
                ], 206);
            }

            if (auth()->user()->profilable->ministere?->id !== $noteConceptuelle->projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            return (new $this->resourceClass($noteConceptuelle->load("fichiers", "projet", "historique_des_notes_conceptuelle")))
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

            if (auth()->user()->profilable?->ministere?->id !== $noteConceptuelle->projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
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
     * Créer une évaluation pour une note conceptuelle
     */
    public function creerEvaluation(int $noteConceptuelleId, array $data): JsonResponse
    {
        try {
            if (auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            if (!auth()->user()->hasPermissionTo('evaluer-une-note-conceptulle')) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            $noteConceptuelle = $this->repository->findOrFail($noteConceptuelleId);

            // Vérifier que la note conceptuelle est soumise (statut = 1)
            if ($noteConceptuelle->statut != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'La note conceptuelle doit être soumise avant de pouvoir être évaluée.'
                ], 403);
            }

            // Vérifier que le projet est au bon statut
            if ($noteConceptuelle->projet->statut->value != StatutIdee::EVALUATION_NOTE->value && ($noteConceptuelle->projet->statut->value != StatutIdee::R_VALIDATION_NOTE_AMELIORER->value) && ($noteConceptuelle->projet->statut->value == StatutIdee::R_VALIDATION_NOTE_AMELIORER->value && !$noteConceptuelle->evaluationTermine())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de redaction de la note conceptuelle.'
                ], 403);
            }

            if ($data["evaluer"]) {
                // Enregistrer les appréciations pour chaque champ
                if (!isset($data['evaluations_champs'])) {
                    throw ValidationException::withMessages(["evaluations_champs" => "Veuillez apprecier le canevas "]);
                }
            }

            $evaluationEnCours = $noteConceptuelle->evaluationEnCours();

            /* if(isset($data["numero_dossier"])){
                $noteConceptuelle->update([
                    "numero_dossier" => $data["numero_dossier"]
                ]);
            }

            if(isset($data["numero_contrat"])){
                $noteConceptuelle->update([
                    "numero_contrat" => $data["numero_contrat"]
                ]);
            } */

            if (isset($data["accept_term"])) {
                $noteConceptuelle->update([
                    "accept_term" => $data["accept_term"]
                ]);
            }

            if (!$evaluationEnCours) {

                $evaluationTermine = $noteConceptuelle->evaluationTermine();

                // Vérifier si une évaluation est déjà terminée (sauf pour les resoumissions)
                if ($evaluationTermine && !$noteConceptuelle->parentId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Une évaluation a déjà été terminée pour cette note conceptuelle. Impossible de créer une nouvelle évaluation.'
                    ], 403);
                }

                // Créer la nouvelle évaluation
                $evaluationData = [
                    'type_evaluation' => "note-conceptuelle",
                    'evaluateur_id' => auth()->id(),
                    "evaluation" => [],
                    "resultats_evaluation" => [],
                    'date_debut_evaluation' => now(),
                    'date_fin_evaluation' => $data["evaluer"] ? now() : null,
                    'statut' => $data["evaluer"] ? 1 : 0, // En cours
                    'id_evaluation' => $evaluationTermine ? $evaluationTermine->id : null // Lien vers le parent
                ];

                $evaluationEnCours = $noteConceptuelle->evaluations()->create($evaluationData);

                // Enregistrer les appréciations pour chaque champ
                if (isset($data['evaluations_champs'])) {
                    $this->sauvegarderEvaluation($evaluationEnCours, $data['evaluations_champs']);
                }


                //if ($data["evaluer"]) {

                $evaluationEnCours->refresh();

                // Calculer les résultats d'examen finaux
                $resultatsExamen = $this->calculerResultatsExamen($noteConceptuelle, $evaluationEnCours);

                // Préparer l'évaluation complète pour enregistrement
                $evaluationComplete = [
                    'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($evaluationEnCours) {
                        $champEvalue = collect($evaluationEnCours->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                        return [
                            'id' => $champEvalue ? $champEvalue['pivot']['id'] : null,
                            'champ_id' => $champ['id'],
                            'label' => $champ['label'],
                            'attribut' => $champ['attribut'],
                            'ordre_affichage' => $champ['ordre_affichage'],
                            'type_champ' => $champ['type_champ'],
                            'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                            'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                            'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                        ];
                    })->toArray(),
                    'statistiques' => $resultatsExamen
                ];

                $evaluationEnCours->fill([
                    'date_fin_evaluation' => $data["evaluer"] ? now() : null,
                    'statut' => $data["evaluer"] ? 1 : 0, // En cours
                    'resultats_evaluation' => $resultatsExamen,
                    'evaluation' => $evaluationComplete,
                ]);

                $evaluationEnCours->save();
                //}
            } else {

                // Enregistrer les appréciations pour chaque champ
                if (isset($data['evaluations_champs'])) {
                    $this->sauvegarderEvaluation($evaluationEnCours, $data['evaluations_champs']);
                }

                $evaluationEnCours->refresh();

                //if ($data["evaluer"]) {

                // Calculer les résultats d'examen finaux
                $resultatsExamen = $this->calculerResultatsExamen($noteConceptuelle, $evaluationEnCours);

                // Préparer l'évaluation complète pour enregistrement
                $evaluationComplete = [

                    'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($evaluationEnCours) {
                        $champEvalue = collect($evaluationEnCours->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                        return [
                            'id' => $champEvalue ? $champEvalue['pivot']['id'] : null,
                            'champ_id' => $champ['id'],
                            'label' => $champ['label'],
                            'attribut' => $champ['attribut'],
                            'ordre_affichage' => $champ['ordre_affichage'],
                            'type_champ' => $champ['type_champ'],
                            'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                            'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                            'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                        ];
                    })->toArray(),
                    'statistiques' => $resultatsExamen
                ];

                $evaluationEnCours->fill([
                    'date_fin_evaluation' => $data["evaluer"] ? now() : null,
                    'statut' => $data["evaluer"] ? 1 : 0, // En cours
                    'resultats_evaluation' => $resultatsExamen,
                    'evaluation' => $evaluationComplete,
                ]);

                $evaluationEnCours->save();
                //}
            }

            // Enregistrer la raison globale si fournie
            if (isset($data['raison'])) {
                $evaluationEnCours->fill(['commentaire' => $data['raison']]);
                $evaluationEnCours->save();
            }

            $evaluationEnCours->refresh();

            $noteConceptuelle->canevas_appreciation_note_conceptuelle = (new CanevasAppreciationTdrResource($this->documentRepository->getCanevasAppreciationNoteConceptuelle()))->toArray(request());
            $noteConceptuelle->save();

            DB::commit();

            $isNewEvaluation = !$noteConceptuelle->evaluationEnCours();
            $message = $data['evaluer'] ?
                'Évaluation finalisée avec succès.' : ($isNewEvaluation ? 'Évaluation créée avec succès.' : 'Appréciations sauvegardées avec succès.');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'evaluation_id'         => $evaluationEnCours->id,
                    'evaluation'            => $resultatsExamen,
                    'statut'                => $evaluationEnCours->statut,
                    'appreciations'         => $evaluationEnCours,
                    'appreciations_count'   => count($data['evaluations_champs'] ?? []),
                    'finalise'              => $data['evaluer'] ?? false
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

    private function sauvegarderEvaluation($evaluation, $champs_evalue)
    {
        // Enregistrer les appréciations pour chaque champ
        if (isset($champs_evalue)) {

            $syncData = [];

            foreach ($champs_evalue as $evaluationChamp) {
                $syncData[$evaluationChamp['champ_id']] = [
                    'note' => $evaluationChamp['appreciation'],
                    'date_note' => now(),
                    'commentaires' => $evaluationChamp['commentaire'] ?? null,
                ];
            }

            $evaluation->champs_evalue()->syncWithoutDetaching($syncData);
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

            $evaluation = $noteConceptuelle->evaluationEnCours();

            if (!$evaluation) {
                $evaluation = $noteConceptuelle->evaluationTermine();

                if (!$evaluation) {
                    return response()->json([
                        'success' => true,
                        'data' => null,
                        'message' => 'Aucune évaluation trouvée pour cette note conceptuelle.'
                    ], 206);
                }
            }

            if (!$evaluation) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucune évaluation trouvée pour cette note conceptuelle.'
                ], 206);
            }

            // Calculer les résultats d'examen
            $resultatsExamen = $evaluation->statut ? $evaluation->resultats_evaluation :  $this->calculerResultatsExamen($noteConceptuelle, $evaluation);

            return response()->json([
                'success' => true,
                'data' => [
                    'note_conceptuelle' => new $this->resourceClass($noteConceptuelle->load("projet", "historique_des_evaluations_notes_conceptuelle")),
                    'evaluation' => [
                        'id' => $evaluation->id,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->valider_par,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $resultatsExamen, //($evaluation->statut && $noteConceptuelle->projet->statut != StatutIdee::EVALUATION_NOTE) ? $evaluation->resultats_evaluation : $resultatsExamen,
                        'statut' => $evaluation->statut,
                        //'champs' => collect($noteConceptuelle->note_conceptuelle)->map(function ($champ) use ($evaluation) {
                        'champs' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($evaluation) {
                            $champ_evalue = collect($evaluation->champs_evalue)
                                ->firstWhere('attribut', $champ["attribut"]);
                            return [
                                'id' => $champ_evalue ? $champ_evalue['pivot']['id'] : null,
                                'champ_id' => $champ["id"],
                                'label' => $champ["label"],
                                'attribut' => $champ["attribut"],
                                'valeur' => $champ["valeur"],
                                'appreciation' => $champ_evalue ? $champ_evalue["pivot"]["note"] : null,
                                'commentaire' => $champ_evalue ? $champ_evalue["pivot"]["commentaires"] : null,
                                'date_note' => $champ_evalue ? $champ_evalue["pivot"]["date_note"] : null,
                                'updated_at' => $champ_evalue ? $champ_evalue["pivot"]["updated_at"] : null,
                            ];
                        }),
                        'historique_evaluations' => $evaluation->historique_evaluations,//EvaluationResource::collection($evaluation->historique_evaluations)
                    ],
                    'resultats_examen' =>  $resultatsExamen, //($evaluation->statut && $noteConceptuelle->projet->statut != StatutIdee::EVALUATION_NOTE) ? $evaluation->resultats_evaluation : $resultatsExamen
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
        //$champs = collect($noteConceptuelle->note_conceptuelle);

        $champs = collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) {
            return [
                'id' => $champ->id,
                'label' => $champ->label,
                'attribut' => $champ->attribut,
                'ordre_affichage' => $champ->ordre_affichage,
                'type_champ' => $champ->type_champ
            ];
        });

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
     * Calculer les résultats d'examen selon vos critères spécifiés
     */
    private function calculerResultatsControleQualite(Rapport $rapport, $evaluation): array
    {
        // Récupérer toutes les appréciations
        //$champs = collect($noteConceptuelle->note_conceptuelle);

        $champs = collect($this->documentRepository->getCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire()->all_champs)->map(function ($champ) {
            return [
                'id' => $champ->id,
                'label' => $champ->label,
                'attribut' => $champ->attribut,
                'ordre_affichage' => $champ->ordre_affichage,
                'type_champ' => $champ->type_champ
            ];
        });

        $champsEvalues = collect($evaluation->champs_evalue);

        // Compter par type d'appréciation
        $nombrePassable = 0;
        $nombreRenvoyer = 0;
        $nombreNonAccepte = 0;
        $nombreNonApplicable = 0;
        $nombreNonEvalues = 0;
        $champsNonCompletes = 0;
        $champsObligatoiresNonEvalues = 0;

        foreach ($champs as $champ) {
            $champEvalue = $champsEvalues->firstWhere('attribut', $champ['attribut']);
            $appreciation = $champEvalue ? $champEvalue['pivot']['note'] : null;

            if ($appreciation) {
                switch (strtolower($appreciation)) {
                    case 'passable':
                        $nombrePassable++;
                        break;
                    case 'renvoyer':
                        $nombreRenvoyer++;
                        break;
                    case 'non accepté':
                    case 'non-accepte':
                    case 'non_accepte': // compatibilité
                        $nombreNonAccepte++;
                        break;
                    case 'non applicable':
                    case 'non-applicable':
                    case 'non_applicable': // compatibilité
                        $nombreNonApplicable++;
                        break;
                }
            } else {
                $nombreNonEvalues++;
                $champsNonCompletes++;
            }
        }

        $totalChamps = $champs->count();

        // Calcul du pourcentage global ou d’évolution (si applicable)
        $pourcentageEvolution = $this->calculerPourcentageEvolutionQC(
            $totalChamps,
            $nombrePassable,
            $nombreRenvoyer,
            $nombreNonAccepte,
            $nombreNonApplicable,
            $nombreNonEvalues
        );

        // Par défaut, résultat null
        $resultat_global = null;
        $message_resultat = null;
        $raisons = null;
        $recommandations = null;
        $resume = null;

        // Si l'évaluation est terminée (statut = 1), calculer le résultat final
        if ($evaluation->statut == 1) {
            $resultat = $this->determinerResultatCQ([
                'passable' => $nombrePassable,
                'renvoyer' => $nombreRenvoyer,
                'non_accepte' => $nombreNonAccepte,
                'non_applicable' => $nombreNonApplicable,
                'non_completees' => $champsNonCompletes,
                'non_evalues' => $nombreNonEvalues,
                'obligatoires_non_evalues' => $champsObligatoiresNonEvalues,
                'total' => $totalChamps
            ]);

            $resultat_global = $resultat['statut'];
            $message_resultat = $resultat['message'];
            $raisons = $resultat['raisons'];
            $recommandations = $resultat['recommandations'];
            $resume = $this->genererResumeExamenQC(
                $resultat,
                $nombrePassable,
                $nombreRenvoyer,
                $nombreNonAccepte,
                $nombreNonApplicable,
                $pourcentageEvolution
            );
        }

        return [
            'nombre_passable' => $nombrePassable,
            'nombre_renvoyer' => $nombreRenvoyer,
            'nombre_non_accepte' => $nombreNonAccepte,
            'nombre_non_applicable' => $nombreNonApplicable,
            'nombre_non_evalues' => $nombreNonEvalues,
            'champs_non_completes' => $champsNonCompletes,
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

        // Pourcentage de rejet (non-accepte / total évalué)
        $pourcentageRejet = $champsEvalues > 0 ? round(($nombreNonAccepte / $champsEvalues) * 100, 2) : 0;

        // Progression globale (pondérée : passe = 1, retour = 0.5, non-accepte = 0)
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
     * Calculer le pourcentage d'évolution de l'évaluation
     */
    private function calculerPourcentageEvolutionQC(
        int $totalChamps,
        int $nombrePassable,
        int $nombreRenvoyer,
        int $nombreNonAccepte,
        int $nombreNonApplicable,
        int $nombreNonEvalues
    ): array {
        if ($totalChamps === 0) {
            return [
                'pourcentage_evaluation' => 0,
                'pourcentage_reussite' => 0,
                'pourcentage_amelioration' => 0,
                'pourcentage_rejet' => 0,
                'pourcentage_non_applicable' => 0,
                'progression_globale' => 0
            ];
        }

        // Champs évalués (tout sauf non évalués)
        $champsEvalues = $nombrePassable + $nombreRenvoyer + $nombreNonAccepte + $nombreNonApplicable;

        // Pourcentage d'évaluation (champs évalués / total)
        $pourcentageEvaluation = round(($champsEvalues / $totalChamps) * 100, 2);

        // Pourcentage de réussite (passe / total évalué)
        $pourcentageReussite = $champsEvalues > 0 ? round(($nombrePassable / $champsEvalues) * 100, 2) : 0;

        // Pourcentage d'amélioration (retour / total évalué)
        $pourcentageAmelioration = $champsEvalues > 0 ? round(($nombreRenvoyer / $champsEvalues) * 100, 2) : 0;

        // Pourcentage de rejet (non-accepte / total évalué)
        $pourcentageRejet = $champsEvalues > 0 ? round(($nombreNonAccepte / $champsEvalues) * 100, 2) : 0;

        // Pourcentage de non applicable
        $pourcentageNonApplicable = $champsEvalues > 0 ? round(($nombreNonApplicable / $champsEvalues) * 100, 2) : 0;

        // Progression globale pondérée
        // Pondération : Passe = 1 | Retour = 0.5 | Non applicable = 0.75 | Non accepté = 0
        $scoreGlobal = ($nombrePassable * 1) + ($nombreRenvoyer * 0.5) + ($nombreNonApplicable * 0.75);
        $progressionGlobale = round(($scoreGlobal / $totalChamps) * 100, 2);

        return [
            'pourcentage_evaluation' => $pourcentageEvaluation,           // % de champs évalués
            'pourcentage_reussite' => $pourcentageReussite,               // % de réussite sur les évalués
            'pourcentage_amelioration' => $pourcentageAmelioration,       // % nécessitant amélioration
            'pourcentage_rejet' => $pourcentageRejet,                     // % rejetés
            'pourcentage_non_applicable' => $pourcentageNonApplicable,    // % non applicables
            'progression_globale' => $progressionGlobale,                 // Score global pondéré
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
            /*
                $canevas = $this->documentRepository->getModel()
                    ->where('type', 'formulaire')
                    ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-redaction-note-conceptuelle'))
                    ->orderBy('created_at', 'desc')
                    ->first();
            */
            $canevas = $this->documentRepository->getCanevasAppreciationNoteConceptuelle();
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
        if ($compteurs['retour'] >= 6) {
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
     * Déterminer le résultat d'examen selon les règles métier (Critères de qualité)
     *
     * Notes possibles :
     * - Passable
     * - Renvoyer
     * - Non accepté
     * - Non applicable
     *
     * Critères de rejet :
     *  - Plus de 4 notes « Renvoyer »
     *  - Plus de 2 notes « Non accepté »
     *  - 1 ou plusieurs questions non complétées
     */
    private function determinerResultatCQ(array $compteurs): array
    {
        // Règle 1 : Si une ou plusieurs questions non complétées
        if (($compteurs['non_completees'] ?? 0) > 0) {
            return [
                'statut' => 'non_accepte',
                'message' => 'Non accepté',
                'raisons' => ["{$compteurs['non_completees']} question(s) non complétée(s)"],
                'recommandations' => ["Compléter toutes les questions avant soumission"]
            ];
        }

        // Règle 2 : Si plus de 2 notes « Non accepté »
        if (($compteurs['non_accepte'] ?? 0) > 2) {
            return [
                'statut' => 'non_accepte',
                'message' => 'Non accepté',
                'raisons' => ["{$compteurs['non_accepte']} note(s) « Non accepté » (seuil maximum : 2)"],
                'recommandations' => ["Revoir en priorité les critères jugés « Non accepté »"]
            ];
        }

        // Règle 3 : Si plus de 4 notes « Renvoyer »
        if (($compteurs['renvoyer'] ?? 0) > 4) {
            return [
                'statut' => 'renvoyer',
                'message' => 'Renvoyer',
                'raisons' => ["{$compteurs['renvoyer']} note(s) « Renvoyer » (seuil maximum : 4)"],
                'recommandations' => ["Réviser les sections marquées comme « Renvoyer » avant nouvelle soumission"]
            ];
        }

        // Règle 4 : Si toutes les questions sont « Passable »
        if (($compteurs['passable'] ?? 0) === ($compteurs['total'] ?? 0)) {
            return [
                'statut' => 'passe',
                'message' => 'L’examen est réussi (toutes les notes sont « Passable »)',
                'raisons' => [],
                'recommandations' => []
            ];
        }

        // Règle 5 : Si toutes les questions sont « Non applicable »
        if (($compteurs['non_applicable'] ?? 0) === ($compteurs['total'] ?? 0)) {
            return [
                'statut' => 'passe',
                'message' => 'Aucune évaluation applicable (toutes les questions sont « Non applicable »)',
                'raisons' => [],
                'recommandations' => []
            ];
        }

        // Sinon : Retour pour amélioration
        $recommandations = [];
        if (($compteurs['renvoyer'] ?? 0) > 0) {
            $recommandations[] = "Améliorer les {$compteurs['renvoyer']} critère(s) marqué(s) comme « Renvoyer »";
        }
        if (($compteurs['non_accepte'] ?? 0) > 0) {
            $recommandations[] = "Corriger les {$compteurs['non_accepte']} critère(s) jugé(s) « Non accepté »";
        }
        if (($compteurs['non_evalues'] ?? 0) > 0) {
            $recommandations[] = "Attendre l’évaluation des {$compteurs['non_evalues']} critère(s) restant(s)";
        }

        return [
            'statut' => 'retour',
            'message' => 'Retour pour amélioration (contient des notes à corriger ou à compléter)',
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
     * Générer le résumé d'examen formaté
     */
    private function genererResumeExamenQC(
        array $resultat,
        int $nombrePasse,
        int $nombreRetour,
        int $nombreNonAccepte,
        int $nombreNonApplicable,
        array $pourcentageEvolution
    ): string {
        $resume = "RÉSUMÉ DE L'ÉVALUATION\n\n";

        $resume .= "Détails des résultats :\n";
        $resume .= "• Nombre de Passable : {$nombrePasse} ({$pourcentageEvolution['pourcentage_reussite']}%)\n";
        $resume .= "• Nombre de Retour : {$nombreRetour} ({$pourcentageEvolution['pourcentage_amelioration']}%)\n";
        $resume .= "• Nombre de Non accepté : {$nombreNonAccepte} ({$pourcentageEvolution['pourcentage_rejet']}%)\n";
        $resume .= "• Nombre de Non applicable : {$nombreNonApplicable} ({$pourcentageEvolution['pourcentage_non_applicable']}%)\n\n";

        $resume .= "Statistiques d'évolution :\n";
        $resume .= "• Progression d'évaluation : {$pourcentageEvolution['pourcentage_evaluation']}%\n";
        $resume .= "• Progression globale : {$pourcentageEvolution['progression_globale']}%\n";
        $resume .= "• Statut : " . ucfirst(str_replace('_', ' ', $pourcentageEvolution['statut_progression'])) . "\n\n";

        $resume .= "Résultat global de l'examen :\n\n";

        switch ($resultat['statut']) {
            case 'passe':
                $resume .= "(✓) Pertinence climatique passable — la présélection a été un succès.\n\n";
                $resume .= "( ) Retour pour un travail supplémentaire\n";
                $resume .= "( ) Non accepté\n";
                $resume .= "( ) Non applicable\n";
                break;

            case 'retour':
                $resume .= "( ) Pertinence climatique passable — la présélection a été un succès.\n\n";
                $resume .= "(✓) Retour pour un travail supplémentaire — certaines sections nécessitent une révision.\n";
                $resume .= "Recommandations d'amélioration :\n";
                foreach ($resultat['recommandations'] as $recommandation) {
                    $resume .= "- {$recommandation}\n";
                }
                $resume .= "\n( ) Non accepté\n";
                $resume .= "( ) Non applicable\n";
                break;

            case 'non_accepte':
                $resume .= "( ) Pertinence climatique passable — la présélection a été un succès.\n\n";
                $resume .= "( ) Retour pour un travail supplémentaire\n";
                $resume .= "(✓) Non accepté — évaluation rejetée.\n";
                $resume .= "( ) Non applicable\n\n";
                $resume .= "Raison(s) du rejet :\n";
                foreach ($resultat['raisons'] as $raison) {
                    $resume .= "- {$raison}\n";
                }
                $resume .= "\nSeules raisons possibles de rejet :\n";
                $resume .= "1. Des questions obligatoires non complétées\n";
                $resume .= "2. Une ou plusieurs réponses évaluées comme « Non accepté »\n";
                $resume .= "3. Un nombre élevé de réponses évaluées comme « Retour »\n";
                break;

            case 'non_applicable':
                $resume .= "( ) Pertinence climatique passable — la présélection a été un succès.\n\n";
                $resume .= "( ) Retour pour un travail supplémentaire\n";
                $resume .= "( ) Non accepté\n";
                $resume .= "(✓) Non applicable — certains critères ne s'appliquent pas au projet.\n";
                $resume .= "Note : Les champs non applicables n’affectent pas négativement la progression globale.\n";
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
                ], 403);
            }

            // Vérifier si l'évaluation a déjà été confirmée (éviter les soumissions multiples)
            if ($evaluation->valider_par && $evaluation->valider_le) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette appreciation a déjà été confirmée. Les soumissions multiples ne sont pas autorisées.'
                ], 403);
            }

            // Récupérer la note conceptuelle
            $noteConceptuelle = $this->repository->find($evaluation->projetable_id);

            if (!$noteConceptuelle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note conceptuelle non trouvée.'
                ], 404);
            }

            if ($noteConceptuelle->statut != 1) {
                return response()->json([
                    'success' => false,
                    'message' => "La note conceptuelle doit être soumise avant de pouvoir confirmer le résultat d'evaluation."
                ], 403);
            }

            // Vérifier si l'évaluation a déjà été confirmée (éviter les soumissions multiples)
            if ($noteConceptuelle->valider_par && $noteConceptuelle->valider_le) {
                return response()->json([
                    'success' => false,
                    'message' => "Cette note conceptuelle a déjà été apprecié. L'appreciation d'une note conceptuelle s'effectue qu'une seule fois."
                ], 403);
            }

            // Calculer les résultats d'examen finaux
            $resultatsExamen = /* (is_array($evaluation->resultats_evaluation) && !empty($evaluation->resultats_evaluation)) ? $evaluation->resultats_evaluation :  */ $this->calculerResultatsExamen($noteConceptuelle, $evaluation);

            // Mettre à jour l'évaluation avec les données complètes
            $evaluation->fill([
                'valider_par' => auth()->id(),
                'valider_le' => now(),
                'commentaire' => ($evaluation->commentaire ?? '') . "\n\nCommentaire de confirmation: " . ($data['commentaire_confirmation'] ?? '')
            ]);

            $evaluation->save();

            // Enregistrer la décision et la raison dans la note conceptuelle
            $noteConceptuelleUpdate = [
                'decision' => $resultatsExamen['resultat_global'],
                'raison_decision' => $data['commentaire_confirmation'] ?? $resultatsExamen['message_resultat'],
                'valider_par' => auth()->id(),
                'valider_le' => now()
            ];

            $noteConceptuelleData = [];
            $noteConceptuelleUpdate['statut'] = 1; // Acceptée

            // Mettre à jour le statut de la note selon le résultat
            switch ($resultatsExamen['resultat_global']) {
                case 'passe':
                    //$noteConceptuelleUpdate['statut'] = 1; // Acceptée
                    $noteConceptuelleData = [
                        'statut' => StatutIdee::VALIDATION_PROFIL,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_PROFIL),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_PROFIL),
                    ];
                    break;
                case 'retour':

                    /**
                     * Ici on crée une nouvelle note conceptuelle en se basant sur la note actuelle
                     * mais avec le statut remis à zéro (brouillon) et en liant la nouvelle note à l'ancienne
                     * via le champ parentId. Cela permet de garder une trace de l'historique des notes conceptuelles
                     * et de leurs révisions. La nouvelle note est créée avec les mêmes données que l'ancienne,
                     * mais avec un statut de brouillon pour indiquer qu'elle est en cours de révision.
                     * Le projet est également mis à jour pour refléter le nouveau statut de la note conceptuelle
                     * (en révision) et la phase/sous-phase appropriée.
                     * La raison de cette approche est de conserver l'historique complet des notes conceptuelles
                     * et de leurs évaluations, tout en permettant à l'auteur de la note de retravailler sur une nouvelle version
                     */

                    /**
                     * On va utiliser la fonction eloquent replicate() pour cloner la note conceptuelle
                     * et ensuite on modifie les champs nécessaires avant de sauvegarder la nouvelle note.
                     */
                    $noteConceptuelle->refresh();
                    $newNote = $noteConceptuelle->replicate();

                    $newNote->statut = 0; // Brouillon
                    $newNote->decision = [];
                    $newNote->accept_term = false;
                    $newNote->parentId = $noteConceptuelle->id;
                    $newNote->rediger_par =  $noteConceptuelle->redacteur->id;
                    $newNote->created_at = now();
                    $newNote->updated_at = null;

                    // Copier les canevas de la note originale vers la nouvelle note
                    $newNote->canevas_redaction_note_conceptuelle = $noteConceptuelle->canevas_redaction_note_conceptuelle;
                    $newNote->canevas_appreciation_note_conceptuelle = $noteConceptuelle->canevas_appreciation_note_conceptuelle;
                    $newNote->save();

                    // Créer une nouvelle évaluation liée à la nouvelle note avec les données de l'ancienne
                    $newEvaluation = $evaluation->replicate();
                    $newEvaluation->projetable_id = $newNote->id;
                    $newEvaluation->projetable_type = get_class($newNote);
                    $newEvaluation->id_evaluation = $evaluation->id; // Lien vers l'évaluation parent
                    $newEvaluation->canevas = $evaluation->canevas; // Copier le canevas
                    $newEvaluation->statut = 0; // En cours
                    $newEvaluation->date_debut_evaluation = now();
                    $newEvaluation->date_fin_evaluation = null;
                    $newEvaluation->valider_le = null;
                    $newEvaluation->valider_par = null;
                    $newEvaluation->resultats_evaluation = [];

                    /* Copier UNIQUEMENT les champs marqués comme "passé" de l'ancienne évaluation
                    $ancienneEvaluation = $evaluation->evaluation ?? [];
                    $champsEvaluesAnciens = $ancienneEvaluation['champs_evalues'] ?? [];

                    // Filtrer pour ne garder que les champs "passé"
                    $champsPassesUniquement = collect($champsEvaluesAnciens)->filter(function ($champ) {
                        return isset($champ['appreciation']) && $champ['appreciation'] === 'passe';
                    })->values()->toArray();

                    // Recalculer les statistiques basées uniquement sur les champs passés
                    $nombrePasse = count($champsPassesUniquement);
                    $totalChamps = collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->count();

                    $statistiquesRecalculees = [
                        'nombre_passe' => $nombrePasse,
                        'nombre_retour' => 0,
                        'nombre_non_accepte' => 0,
                        'nombre_non_evalues' => $totalChamps - $nombrePasse,
                        'total_champs' => $totalChamps,
                        'champs_obligatoires_non_evalues' => 0,
                        'resultat_global' => null,
                        'message_resultat' => null,
                        'raisons' => null,
                        'recommandations' => null,
                        'resume' => null
                    ];

                    $newEvaluation->evaluation = [
                        'champs_evalues' => $champsPassesUniquement,
                        'statistiques' => $statistiquesRecalculees
                    ];

                    $newEvaluation->resultats_evaluation = $statistiquesRecalculees;

                    $newEvaluation->created_at = now();
                    $newEvaluation->updated_at = null;
                    $newEvaluation->save();

                    // Copier également les relations champs_evalue UNIQUEMENT pour les champs "passé"
                    $champsEvalues = $evaluation->champs_evalue;
                    foreach ($champsEvalues as $champ) {
                        if (isset($champ->pivot->note) && $champ->pivot->note === 'passe') {
                            $newEvaluation->champs_evalue()->attach($champ->id, [
                                'note' => $champ->pivot->note,
                                'date_note' => $champ->pivot->date_note,
                                'commentaires' => $champ->pivot->commentaires,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    } */

                    // Sauvegarder d'abord la nouvelle évaluation avec des valeurs temporaires
                    $newEvaluation->evaluation = [];
                    $newEvaluation->resultats_evaluation = [];
                    $newEvaluation->created_at = now();
                    $newEvaluation->updated_at = null;
                    $newEvaluation->save();

                    // Copier les relations champs_evalue de l'ancienne évaluation
                    // Pour les champs "passé" : copier tel quel
                    // Pour les autres (retour/non_accepte) : mettre null pour forcer la réévaluation
                    $champsEvalues = $evaluation->champs_evalue;
                    foreach ($champsEvalues as $champ) {
                        $note = $champ->pivot->note;

                        if ($note === 'passe') {
                            // Si passé, copier tel quel
                            $newEvaluation->champs_evalue()->attach($champ->id, [
                                'note' => $note,
                                'date_note' => $champ->pivot->date_note,
                                'commentaires' => $champ->pivot->commentaires,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } else {
                            // Si retour ou non_accepte, mettre null (pas de copie dans pivot)
                            // Les anciennes valeurs seront dans le JSON evaluation avec le suffixe "_passer"
                        }
                    }

                    // Recharger pour avoir accès aux relations
                    $newEvaluation->refresh();

                    // Construire le JSON evaluation basé sur les champs copiés
                    $resultatsExamen = $this->calculerResultatsExamen($newNote, $newEvaluation);

                    // Récupérer l'ancienne évaluation pour référence
                    $ancienneEvaluation = $evaluation->evaluation ?? [];
                    $anciensChampsEvalues = collect($ancienneEvaluation['champs_evalues'] ?? []);

                    $evaluationComplete = [
                        'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($newEvaluation, $anciensChampsEvalues) {
                            $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                            $ancienChampEvalue = $anciensChampsEvalues->firstWhere('attribut', $champ['attribut']);

                            $result = [
                                'id' => $champEvalue ? $champEvalue['pivot']['id'] : null,
                                'champ_id' => $champ['id'],
                                'label' => $champ['label'],
                                'attribut' => $champ['attribut'],
                                'ordre_affichage' => $champ['ordre_affichage'],
                                'type_champ' => $champ['type_champ'],
                                'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                                'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                                'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                            ];

                            // Si le champ n'est pas dans la nouvelle évaluation mais existe dans l'ancienne
                            // C'est un champ qui n'était pas "passé", on ajoute les anciennes valeurs avec "_passer"
                            if (!$champEvalue && $ancienChampEvalue) {
                                $result['appreciation_passer'] = $ancienChampEvalue['appreciation'] ?? null;
                                $result['commentaire_passer_evaluateur'] = $ancienChampEvalue['commentaire_evaluateur'] ?? null;
                                $result['date_appreciation_passer'] = $ancienChampEvalue['date_appreciation'] ?? null;
                            }

                            return $result;
                        })->toArray(),
                        'statistiques' => $resultatsExamen
                    ];

                    // Mettre à jour avec les données complètes
                    $newEvaluation->evaluation = $evaluationComplete;
                    $newEvaluation->resultats_evaluation = $resultatsExamen;
                    $newEvaluation->save();

                    $noteConceptuelleData = [
                        'statut' => StatutIdee::R_VALIDATION_NOTE_AMELIORER,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::R_VALIDATION_NOTE_AMELIORER),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::R_VALIDATION_NOTE_AMELIORER),
                    ];
                    break;
                case 'non_accepte':
                    //$noteConceptuelleUpdate['statut'] = -1; // Rejetée

                    $newNote = $noteConceptuelle->replicate();

                    $newNote->statut = 0; // Brouillon
                    $newNote->decision = [];
                    $newNote->accept_term = false;
                    $newNote->parentId = $noteConceptuelle->id;
                    $newNote->rediger_par =  $noteConceptuelle->redacteur->id;
                    $newNote->created_at = now();
                    $newNote->updated_at = null;

                    /*
                        $noteData = [
                            'intitule' => "",
                            'statut' => 0,
                            'decision' => [],
                            'numero_contrat' => null,
                            'numero_dossier' => null,
                            'accept_term' => false,
                            'rediger_par' =>  $noteConceptuelle->rediger_par,
                            'updated_at' => null,
                            'canevas_redaction_note_conceptuelle' => [],
                            'canevas_appreciation_note_conceptuelle' => [],
                            'parentId' => $noteConceptuelle->id,
                            'projetId' => $noteConceptuelle->projetId,
                        ];
                        $noteConceptuelle->projet->noteConceptuelle()->create($noteData);
                    */

                    // Copier les canevas de la note originale vers la nouvelle note
                    $newNote->canevas_redaction_note_conceptuelle = $noteConceptuelle->canevas_redaction_note_conceptuelle;
                    $newNote->canevas_appreciation_note_conceptuelle = $noteConceptuelle->canevas_appreciation_note_conceptuelle;
                    $newNote->save();

                    // Créer une nouvelle évaluation liée à la nouvelle note avec les données de l'ancienne
                    $newEvaluation = $evaluation->replicate();
                    $newEvaluation->projetable_id = $newNote->id;
                    $newEvaluation->projetable_type = get_class($newNote);
                    $newEvaluation->id_evaluation = $evaluation->id; // Lien vers l'évaluation parent
                    $newEvaluation->canevas = $evaluation->canevas; // Copier le canevas
                    $newEvaluation->statut = 0; // En cours
                    $newEvaluation->date_debut_evaluation = now();
                    $newEvaluation->date_fin_evaluation = null;
                    $newEvaluation->valider_le = null;
                    $newEvaluation->valider_par = null;
                    $newEvaluation->resultats_evaluation = [];

                    /* Copier UNIQUEMENT les champs marqués comme "passé" de l'ancienne évaluation
                    $ancienneEvaluation = $evaluation->evaluation ?? [];
                    $champsEvaluesAnciens = $ancienneEvaluation['champs_evalues'] ?? [];

                    // Filtrer pour ne garder que les champs "passé"
                    $champsPassesUniquement = collect($champsEvaluesAnciens)->filter(function ($champ) {
                        return isset($champ['appreciation']) && $champ['appreciation'] === 'passe';
                    })->values()->toArray();

                    // Recalculer les statistiques basées uniquement sur les champs passés
                    $nombrePasse = count($champsPassesUniquement);
                    $totalChamps = collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->count();

                    $statistiquesRecalculees = [
                        'nombre_passe' => $nombrePasse,
                        'nombre_retour' => 0,
                        'nombre_non_accepte' => 0,
                        'nombre_non_evalues' => $totalChamps - $nombrePasse,
                        'total_champs' => $totalChamps,
                        'champs_obligatoires_non_evalues' => 0,
                        'resultat_global' => null,
                        'message_resultat' => null,
                        'raisons' => null,
                        'recommandations' => null,
                        'resume' => null
                    ];

                    $newEvaluation->evaluation = [
                        'champs_evalues' => $champsPassesUniquement,
                        'statistiques' => $statistiquesRecalculees
                    ];

                    $newEvaluation->resultats_evaluation = $statistiquesRecalculees;

                    $newEvaluation->created_at = now();
                    $newEvaluation->updated_at = null;
                    $newEvaluation->save();

                    // Copier également les relations champs_evalue UNIQUEMENT pour les champs "passé"
                    $champsEvalues = $evaluation->champs_evalue;
                    foreach ($champsEvalues as $champ) {
                        if (isset($champ->pivot->note) && $champ->pivot->note === 'passe') {
                            $newEvaluation->champs_evalue()->attach($champ->id, [
                                'note' => $champ->pivot->note,
                                'date_note' => $champ->pivot->date_note,
                                'commentaires' => $champ->pivot->commentaires,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    } */

                    // Sauvegarder d'abord la nouvelle évaluation avec des valeurs temporaires
                    $newEvaluation->evaluation = [];
                    $newEvaluation->resultats_evaluation = [];
                    $newEvaluation->created_at = now();
                    $newEvaluation->updated_at = null;
                    $newEvaluation->save();

                    // Copier les relations champs_evalue de l'ancienne évaluation
                    // Pour les champs "passé" : copier tel quel
                    // Pour les autres (retour/non_accepte) : mettre null pour forcer la réévaluation
                    $champsEvalues = $evaluation->champs_evalue;
                    foreach ($champsEvalues as $champ) {
                        $note = $champ->pivot->note;

                        if ($note === 'passe') {
                            // Si passé, copier tel quel
                            $newEvaluation->champs_evalue()->attach($champ->id, [
                                'note' => $note,
                                'date_note' => $champ->pivot->date_note,
                                'commentaires' => $champ->pivot->commentaires,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } else {
                            // Si retour ou non_accepte, mettre null (pas de copie dans pivot)
                            // Les anciennes valeurs seront dans le JSON evaluation avec le suffixe "_passer"
                        }
                    }

                    // Recharger pour avoir accès aux relations
                    $newEvaluation->refresh();

                    // Construire le JSON evaluation basé sur les champs copiés
                    $resultatsExamen = $this->calculerResultatsExamen($newNote, $newEvaluation);

                    // Récupérer l'ancienne évaluation pour référence
                    $ancienneEvaluation = $evaluation->evaluation ?? [];
                    $anciensChampsEvalues = collect($ancienneEvaluation['champs_evalues'] ?? []);

                    $evaluationComplete = [
                        'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($newEvaluation, $anciensChampsEvalues) {
                            $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                            $ancienChampEvalue = $anciensChampsEvalues->firstWhere('attribut', $champ['attribut']);

                            $result = [
                                'id' => $champEvalue ? $champEvalue['pivot']['id'] : null,
                                'champ_id' => $champ['id'],
                                'label' => $champ['label'],
                                'attribut' => $champ['attribut'],
                                'ordre_affichage' => $champ['ordre_affichage'],
                                'type_champ' => $champ['type_champ'],
                                'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                                'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                                'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                            ];

                            // Si le champ n'est pas dans la nouvelle évaluation mais existe dans l'ancienne
                            // C'est un champ qui n'était pas "passé", on ajoute les anciennes valeurs avec "_passer"
                            if (!$champEvalue && $ancienChampEvalue) {
                                $result['appreciation_passer'] = $ancienChampEvalue['appreciation'] ?? null;
                                $result['commentaire_passer_evaluateur'] = $ancienChampEvalue['commentaire_evaluateur'] ?? null;
                                $result['date_appreciation_passer'] = $ancienChampEvalue['date_appreciation'] ?? null;
                            }

                            return $result;
                        })->toArray(),
                        'statistiques' => $resultatsExamen
                    ];

                    // Mettre à jour avec les données complètes
                    $newEvaluation->evaluation = $evaluationComplete;
                    $newEvaluation->resultats_evaluation = $resultatsExamen;
                    $newEvaluation->save();

                    $noteConceptuelleData = [
                        'statut' => StatutIdee::NOTE_CONCEPTUEL,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                    ];
                    break;
            }

            $noteConceptuelle->fill($noteConceptuelleUpdate);

            $noteConceptuelle->save();

            $noteConceptuelle->projet->fill($noteConceptuelleData);

            $noteConceptuelle->projet->save();

            // Enregistrer dans l'historique des décisions si nécessaire
            $this->enregistrerDecisionEvaluation($noteConceptuelle, $resultatsExamen, $data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Résultat de l\'évaluation confirmé avec succès.',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'statut' => $evaluation->statut,
                    'note_conceptuelle_id' => $noteConceptuelle->id,
                    'statut' => $noteConceptuelle->statut,
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

            if (auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            //if (!auth()->user()->hasPermissionTo('evaluer-une-note-conceptulle')) {
            if (!auth()->user()->hasPermissionTo('evaluer-une-note-conceptulle') &&  auth()->user()->type !== 'dgpd') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer la note conceptuelle
            $noteConceptuelle = $this->repository->findOrFail($noteConceptuelleId);

            // Vérifier que le projet est au bon statut
            if ($noteConceptuelle->projet->statut->value != StatutIdee::EVALUATION_NOTE->value && ($noteConceptuelle->projet->statut->value != StatutIdee::R_VALIDATION_NOTE_AMELIORER->value) && !($noteConceptuelle->evaluationTermine())) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => "Le projet n\'est pas à l\'étape d'évaluation de la note conceptuelle."
                ], 403);
            }

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
            // Vérifier les autorisations
            if (auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            if (!auth()->user()->hasPermissionTo('valider-l-etude-de-profil') && auth()->user()->type != 'dgpd') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value != StatutIdee::VALIDATION_PROFIL->value && !($projet->noteConceptuelle->evaluationTermine())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de validation d\'étude de profil.'
                ], 403);
            }

            // Récupérer la note conceptuelle du projet
            $noteConceptuelle = $this->repository->getModel()
                ->where('projetId', $projetId)
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$noteConceptuelle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune note conceptuelle trouvée pour ce projet.'
                ], 404);
            }

            // Valider que l'action choisie est autorisée selon le type de projet (est_dur)
            /*
                $actionsAutorisees = $this->getActionsAutoriseesSelonTypeProjet($projet, $data);

                if (!in_array($data['decision'], $actionsAutorisees)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette action n\'est pas autorisée pour ce type de projet.',
                        'actions_autorisees' => $actionsAutorisees,
                        'est_dur' => $projet->est_dur ? 1 : 0
                    ], 403);
                }
            */

            // Déterminer si c'est une soumission ou un brouillon
            $action = $data['action'] ?? 'submit';
            $estBrouillon = $action === 'draft';
            $checklist_suivi = [];
            $resultatsControleQualite = null;
            $rapportFaisabilitePrelim = null;
            $evaluationRapport = null;

            // Créer une évaluation pour tracer la validation
            $evaluation = $projet->evaluations()->updateOrCreate([
                'type_evaluation' => 'validation-etude-profil',
                'projetable_type' => get_class($projet),
                'projetable_id' => $projet->id,
            ], [
                'date_debut_evaluation' => now(),
                'date_fin_evaluation' => ($action === 'submit' && $data['decision'] !== 'sauvegarder') ? now() : null,
                'evaluateur_id' => auth()->id(),
                'commentaire' => $data['commentaire'] ?? '',
                'statut' => ($action === 'submit' && $data['decision'] !== 'sauvegarder') ? 1 : 0,
                'evaluation' => [],
                'resultats_evaluation' => []
            ]);

            if ($data['decision'] === "faire_etude_faisabilite_preliminaire") {

                if (!$estBrouillon) {
                    if (empty(data_get($data, 'checklist_suivi_rapport_faisabilite_preliminaire'))) {
                        throw ValidationException::withMessages([
                            "checklist_suivi_rapport_faisabilite_preliminaire" => "Veuillez faire le suivi du controle qualite du rapport de faisabilité préliminaire !"
                        ]);
                    }

                    if (!isset($data['analyse_financiere'])) {
                        throw ValidationException::withMessages([
                            "analyse_financiere" => "Veuillez preciser les informations du fond de preparation !"
                        ]);
                    }
                }
                // Récupérer le dernier rapport de préfaisabilité s'il existe
                $rapportExistant = $projet->rapportFaisabilitePreliminaire()->where("statut", "<>", "brouillon")->first();

                if (!$rapportExistant) {
                    throw new Exception("Le rapport de faisabilite preliminaire n'a pas ete soumis", 403);
                }

                // Traiter la checklist de suivi pour la soumission finale
                if (isset($data['checklist_suivi_rapport_faisabilite_preliminaire'])) {
                    $this->traiterChampsChecklistSuivi(
                        $rapportExistant,
                        $data['checklist_suivi_rapport_faisabilite_preliminaire']
                    );

                    $rapportExistant->fresh();
                    $checklist_suivi = $rapportExistant->checklist_suivi;

                    // Créer une évaluation spécifique pour le contrôle qualité du rapport
                    $evaluationRapport = $rapportExistant->evaluations()->updateOrCreate([
                        'type_evaluation' => 'controle-qualite-rapport-faisabilite-preliminaire',
                        'projetable_type' => get_class($rapportExistant),
                        'projetable_id' => $rapportExistant->id,
                    ], [
                        'date_debut_evaluation' => now(),
                        'date_fin_evaluation' => ($action === 'submit' && $data['decision'] !== 'sauvegarder') ? now() : null,
                        'evaluateur_id' => auth()->id(),
                        'commentaire' => $data['commentaire'] ?? '',
                        'statut' => ($action === 'submit' && $data['decision'] !== 'sauvegarder') ? 1 : 0,
                        'evaluation' => [],
                        'resultats_evaluation' => []
                    ]);

                    // Sauvegarder les valeurs de checklist_suivi dans les relations champs_evalue
                    foreach ($checklist_suivi as $item) {
                        $evaluationRapport->champs_evalue()->syncWithoutDetaching([
                            $item['champ_id'] => [
                                'note' => $item['valeur'],
                                'date_note' => $item['updated_at'] ?? now(),
                                'commentaires' => $item['commentaire'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        ]);
                    }

                    $evaluationRapport->refresh();

                    // Calculer le résultat de l'évaluation selon les règles SFD-015
                    $resultatsEvaluation = $this->calculerResultatsControleQualite($rapportExistant, $evaluationRapport);

                    // Stocker pour utilisation ultérieure
                    $resultatsControleQualite = $resultatsEvaluation;
                    $rapportFaisabilitePrelim = $rapportExistant;

                    // Préparer l'évaluation complète pour enregistrement dans l'évaluation du rapport
                    $evaluationComplete = [
                        'champs_evalues' => collect($this->documentRepository->getCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire()->all_champs)->map(function ($champ) use ($evaluationRapport) {
                            $champEvalue = collect($evaluationRapport->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                            return [
                                'id' => $champEvalue ? $champEvalue['pivot']['id'] : null,
                                'champ_id' => $champ['id'],
                                'label' => $champ['label'],
                                'attribut' => $champ['attribut'],
                                'ordre_affichage' => $champ['ordre_affichage'],
                                'type_champ' => $champ['type_champ'],
                                'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                                'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                                'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                            ];
                        })->toArray(),
                        'statistiques' => $resultatsEvaluation,
                        'date_evaluation' => now(),
                        'confirme_par' => ($estBrouillon && $data["decision"] === "") ? new UserResource(auth()->user()) : null
                    ];

                    // Mettre à jour l'évaluation du rapport avec les données complètes
                    $evaluationRapport->fill([
                        'resultats_evaluation' => $resultatsEvaluation,
                        'evaluation' => $evaluationComplete,
                        'commentaire' => $resultatsEvaluation['message_resultat']
                    ]);

                    $evaluationRapport->save();
                }

                // Gérer l'analyse financière et calculer la VAN et le TRI
                if (isset($data['analyse_financiere'])) {
                    $updateData = [];
                    $analyseFinanciere = $data['analyse_financiere'];

                    $requiredFields = ['duree_vie', 'investissement_initial', 'flux_tresorerie', 'taux_actualisation'];

                    foreach ($requiredFields as $field) {
                        // validation de présence de $analyseFinanciere[$field]
                        if (!isset($analyseFinanciere[$field]) && !empty($analyseFinanciere[$field])) {
                            throw ValidationException::withMessages([
                                "analyse_financiere.$field" => "Le champ $field est obligatoire lorsque le projet est financé. " . $analyseFinanciere[$field]
                            ]);
                        }
                        // validations supplémentaires pour les champs spécifiques
                        // Il faut savoir que les donnees sont soumis dans un formdata donc tout est string

                        if ($field === 'duree_vie') {

                            $value = $analyseFinanciere[$field];

                            // Vérifie que c'est bien un nombre ET un entier positif
                            if (!ctype_digit((string)$value) || (int)$value <= 0) {
                                throw ValidationException::withMessages([
                                    "analyse_financiere.$field" => "Le champ $field doit être un nombre entier positif (sans virgule)."
                                ]);
                            }

                            // Optionnel : convertir proprement en entier
                            $analyseFinanciere[$field] = (int)$value;
                        }

                        // Ajouter d'autres validations spécifiques si nécessaire
                        if (in_array($field, ['investissement_initial', 'taux_actualisation'])) {
                            if (!is_numeric($analyseFinanciere[$field])) {
                                throw ValidationException::withMessages([
                                    "analyse_financiere.$field" => "Le champ $field doit être une date valide au format AAAA-MM-JJ."
                                ]);
                            }

                            // Optionnel : forcer la conversion en float si tu veux l'utiliser ensuite
                            $analyseFinanciere[$field] = (float) $analyseFinanciere[$field];
                        }
                    }

                    // Préparer les données pour le fill() et la mise à jour
                    $financialData = [
                        'duree_vie' => $analyseFinanciere['duree_vie'] ?? $projet->duree_vie,
                        'investissement_initial' => $analyseFinanciere['investissement_initial'] ?? $projet->investissement_initial,
                        'flux_tresorerie' => $analyseFinanciere['flux_tresorerie'] ?? $projet->flux_tresorerie,
                        'taux_actualisation' => $analyseFinanciere['taux_actualisation'] ?? $projet->taux_actualisation,
                    ];

                    // Mettre à jour le modèle en mémoire avec les nouvelles données financières
                    $projet->fill($financialData);

                    // Calculer la VAN et le TRI à partir des données mises à jour
                    $van = $projet->calculerVAN();
                    $projet->van = $van;
                    $tri = $projet->calculerTRI();

                    // Ajouter toutes les données financières et les résultats au tableau de mise à jour
                    $updateData = array_merge($updateData, $financialData);

                    if ($van !== null) {
                        $updateData['van'] = $van;
                    }
                    if ($tri !== null) {
                        $updateData['tri'] = $tri;
                    }

                    $projet->update($updateData);
                }

                // Mettre à jour l'évaluation de validation du profil si c'est une soumission finale
                if ($action === 'submit' && $data['decision'] !== 'sauvegarder') {
                    $evaluation->fill([
                        'evaluation' => [
                            'decision' => $data['decision'],
                            'commentaire' => $data['commentaire'] ?? '',
                            'est_a_haut_risque' => $data['est_a_haut_risque'] ?? false,
                            'action' => $data['action'] ?? 'submit',
                            'analyse_financiere' => $data['analyse_financiere'] ?? null,
                            'checklist_suivi_rapport' => $checklist_suivi,
                            'resultats_controle_qualite' => $resultatsControleQualite,
                        ],
                        'resultats_evaluation' => $data['decision'],
                        'valider_le' =>  now(),
                        'valider_par' => auth()->user()->id,
                    ]);

                    $evaluation->save();
                }
            } else if ($action === 'submit' && $data['decision'] !== 'sauvegarder') {
                // Mettre à jour l'évaluation avec les données complètes
                $evaluation->fill([
                    'evaluation' => [
                        'decision' => $data['decision'],
                        'commentaire' => $data['commentaire'] ?? '',
                        'action' => $data['action'] ?? 'submit',
                    ],
                    'resultats_evaluation' => $data['decision'],
                    'valider_le' =>  now(),
                    'valider_par' => auth()->user()->id,
                ]);

                $evaluation->save();
            }

            if ($data['decision'] === "faire_etude_prefaisabilite" && isset($data["est_a_haut_risque"])) {
                $projet->est_a_haut_risque = $data["est_a_haut_risque"];
                $projet->save();
            }

            if ($action === "submit") {

                // Traiter la décision selon le cas d'utilisation
                $nouveauStatut = $this->traiterDecisionValidation($projet, $data['decision'], $data, $noteConceptuelle, $resultatsControleQualite, $rapportFaisabilitePrelim, $evaluation);

                // Traiter la checklist de suivi si la décision est de faire une étude de faisabilité préliminaire

                // Créer une évaluation pour tracer la validation
                /*
                    $projet->evaluations()->create([
                        'type_evaluation' => 'validation-etude-profil',
                        'projetable_type' => get_class($projet),
                        'projetable_id' => $projet->id,
                        'date_debut_evaluation' => now(),
                        'date_fin_evaluation' => now(),
                        'valider_le' => now(),
                        'evaluateur_id' => auth()->id(),
                        'valider_par' => auth()->id(),
                        'commentaire' => $data['commentaire'] ?? '',
                        'evaluation' => [
                            'decision' => $data['decision'],
                            'commentaire' => $data['commentaire'] ?? '',
                            'est_a_haut_risque' => $data['est_a_haut_risque'] ?? false,
                            'checklist_suivi' => $checklistSuivi,
                            'action' => $data['action'] ?? 'submit'
                        ],
                        'resultats_evaluation' => $data['decision'],
                        'statut' => 1
                    ]);
                */

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($projet, $nouveauStatut);
                $this->enregistrerDecision(
                    $projet,
                    "Validation de l'étude de profil - " . ucfirst(str_replace('_', ' ', $data['decision'])),
                    $data['commentaire'] ?? '',
                    auth()->id()
                );

                // Envoyer des notifications si nécessaire
                if ($data['decision'] !== 'sauvegarder') {
                    $this->envoyerNotificationValidation($projet, $data['decision'], $data['commentaire'] ?? '');
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $this->getMessageSuccesValidation($data['decision']),
                'data' => [
                    'evaluation' => $evaluation,
                    'evaluationRapport' => $evaluationRapport,
                    'projet_id' => $projet,
                    'rapport' => $projet->rapportFaisabilitePreliminaire()->first()->load("evaluations"),
                    'ancien_statut' => StatutIdee::VALIDATION_PROFIL->value,
                    'decision' => $data['decision'],
                    'commentaire' => $data['commentaire'] ?? '',
                    'actions_effectuees' => $this->getActionsEffectuees($data['decision'])
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Confirmer la validation de l'étude de profil pour les cas "non accepté"
     * Permet de choisir entre "reviser_note_conceptuelle" ou "abandonner_projet"
     */
    public function confirmerValidationEtudeProfilNonAcceptee(int $projetId, array $data): JsonResponse
    {
        try {
            // Vérifier les autorisations
            if (auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'accès pour effectuer cette action", 403);
            }

            if (!auth()->user()->hasPermissionTo('valider-l-etude-de-profil') && auth()->user()->type != 'dgpd') {
                throw new Exception("Vous n'avez pas les droits d'accès pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value != StatutIdee::VALIDATION_PROFIL->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de validation d\'étude de profil.'
                ], 403);
            }

            // Récupérer l'évaluation de validation
            $evaluation = $projet->evaluations()
                ->where('type_evaluation', 'validation-etude-profil')
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$evaluation || !$evaluation->resultats_evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune évaluation de validation trouvée pour ce projet.'
                ], 404);
            }

            // Vérifier si l'évaluation a déjà été confirmée (éviter les soumissions multiples)
            if ($evaluation->valider_par && $evaluation->valider_le) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette validation a déjà été confirmée. Les soumissions multiples ne sont pas autorisées.'
                ], 403);
            }

            // Vérifier que le résultat est bien "non_accepte"
            $resultatsEvaluation = $evaluation->resultats_evaluation;
            if ($resultatsEvaluation['resultat_global'] !== 'non_accepte') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette méthode n\'est utilisable que pour les cas "non accepté". Le résultat actuel est: ' . $resultatsEvaluation['resultat_global']
                ], 422);
            }

            // Valider l'action demandée pour les cas "non accepté"
            if (!isset($data['action']) || !in_array($data['action'], ['reviser_note_conceptuelle', 'abandonner_projet'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action invalide pour cas "non accepté". Actions possibles: reviser_note_conceptuelle, abandonner_projet.'
                ], 422);
            }

            // Récupérer la note conceptuelle
            $noteConceptuelle = $this->repository->getModel()
                ->where('projetId', $projetId)
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$noteConceptuelle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune note conceptuelle trouvée pour ce projet.'
                ], 404);
            }

            // Vérifier que la note conceptuelle est soumise
            if ($noteConceptuelle->statut != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'La note conceptuelle doit être soumise avant de pouvoir confirmer la validation.'
                ], 403);
            }

            // Mettre à jour l'évaluation avec les informations de confirmation
            $evaluation->fill([
                'valider_par' => auth()->id(),
                'valider_le' => now(),
                'commentaire' => ($evaluation->commentaire ?? '') . "\n\nCommentaire de confirmation: " . ($data['commentaire_confirmation'] ?? 'Action confirmée pour cas non accepté')
            ]);
            $evaluation->save();

            $nouveauStatut = null;
            $messageAction = '';

            switch ($data['action']) {
                case 'reviser_note_conceptuelle':
                    // Réviser la note conceptuelle malgré l'évaluation négative
                    $noteConceptuelle->refresh();
                    $newNote = $noteConceptuelle->replicate();

                    $newNote->statut = 0; // Brouillon
                    $newNote->decision = [];
                    $newNote->accept_term = false;
                    $newNote->parentId = $noteConceptuelle->id;
                    $newNote->rediger_par = $noteConceptuelle->redacteur->id;
                    $newNote->created_at = now();
                    $newNote->updated_at = null;

                    // Copier les canevas de la note originale vers la nouvelle note
                    $newNote->canevas_redaction_note_conceptuelle = $noteConceptuelle->canevas_redaction_note_conceptuelle;
                    $newNote->canevas_appreciation_note_conceptuelle = $noteConceptuelle->canevas_appreciation_note_conceptuelle;
                    $newNote->save();

                    // Récupérer l'évaluation de la note conceptuelle
                    $evaluationNote = $noteConceptuelle->evaluationTermine();

                    if ($evaluationNote) {
                        // Créer une nouvelle évaluation liée à la nouvelle note
                        $newEvaluation = $evaluationNote->replicate();
                        $newEvaluation->projetable_id = $newNote->id;
                        $newEvaluation->projetable_type = get_class($newNote);
                        $newEvaluation->id_evaluation = $evaluationNote->id;
                        $newEvaluation->canevas = $evaluationNote->canevas;
                        $newEvaluation->statut = 0; // En cours
                        $newEvaluation->date_debut_evaluation = now();
                        $newEvaluation->date_fin_evaluation = null;
                        $newEvaluation->valider_le = null;
                        $newEvaluation->valider_par = null;
                        $newEvaluation->resultats_evaluation = [];
                        $newEvaluation->evaluation = [];
                        $newEvaluation->created_at = now();
                        $newEvaluation->updated_at = null;
                        $newEvaluation->save();

                        // Copier les relations champs_evalue de l'ancienne évaluation
                        // Pour les champs "passé" : copier tel quel
                        $champsEvalues = $evaluationNote->champs_evalue;
                        foreach ($champsEvalues as $champ) {
                            $note = $champ->pivot->note;

                            if ($note === 'passe') {
                                $newEvaluation->champs_evalue()->attach($champ->id, [
                                    'note' => $note,
                                    'date_note' => $champ->pivot->date_note,
                                    'commentaires' => $champ->pivot->commentaires,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }

                        $newEvaluation->refresh();

                        // Construire le JSON evaluation basé sur les champs copiés
                        $resultatsExamen = $this->calculerResultatsExamen($newNote, $newEvaluation);

                        // Récupérer l'ancienne évaluation pour référence
                        $ancienneEvaluation = $evaluationNote->evaluation ?? [];
                        $anciensChampsEvalues = collect($ancienneEvaluation['champs_evalues'] ?? []);

                        $evaluationComplete = [
                            'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($newEvaluation, $anciensChampsEvalues) {
                                $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                                $ancienChampEvalue = $anciensChampsEvalues->firstWhere('attribut', $champ['attribut']);

                                $result = [
                                    'id' => $champEvalue ? $champEvalue['pivot']['id'] : null,
                                    'champ_id' => $champ['id'],
                                    'label' => $champ['label'],
                                    'attribut' => $champ['attribut'],
                                    'ordre_affichage' => $champ['ordre_affichage'],
                                    'type_champ' => $champ['type_champ'],
                                    'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                                    'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                                    'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                                ];

                                // Si le champ n'est pas dans la nouvelle évaluation mais existe dans l'ancienne
                                if (!$champEvalue && $ancienChampEvalue) {
                                    $result['appreciation_passer'] = $ancienChampEvalue['appreciation'] ?? null;
                                    $result['commentaire_passer_evaluateur'] = $ancienChampEvalue['commentaire_evaluateur'] ?? null;
                                    $result['date_appreciation_passer'] = $ancienChampEvalue['date_appreciation'] ?? null;
                                }

                                return $result;
                            })->toArray(),
                            'statistiques' => $resultatsExamen
                        ];

                        $newEvaluation->evaluation = $evaluationComplete;
                        $newEvaluation->resultats_evaluation = $resultatsExamen;
                        $newEvaluation->save();
                    }

                    $projet->update([
                        'statut' => StatutIdee::NOTE_CONCEPTUEL,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL)
                    ]);

                    $nouveauStatut = StatutIdee::NOTE_CONCEPTUEL;
                    $messageAction = 'Note conceptuelle envoyée pour révision malgré l\'évaluation négative.';
                    break;

                case 'abandonner_projet':
                    // Abandonner le projet suite à l'évaluation négative
                    $projet->update([
                        'statut' => StatutIdee::ABANDON,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::ABANDON),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::ABANDON)
                    ]);

                    $nouveauStatut = StatutIdee::ABANDON;
                    $messageAction = 'Projet abandonné suite à l\'évaluation négative de l\'étude de profil.';
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Action invalide. Actions possibles: reviser_note_conceptuelle, abandonner_projet.'
                    ], 422);
            }

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, $nouveauStatut);
            $this->enregistrerDecision(
                $projet,
                "Confirmation validation étude de profil (non accepté) - " . ucfirst(str_replace('_', ' ', $data['action'])),
                $data['commentaire'] ?? 'Action confirmée suite à évaluation non acceptée',
                auth()->id()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $messageAction,
                'data' => [
                    'projet_id' => $projet->id,
                    'ancien_statut' => StatutIdee::VALIDATION_PROFIL->value,
                    'nouveau_statut' => $nouveauStatut->value,
                    'action' => $data['action'],
                    'confirme_par' => auth()->id(),
                    'confirme_le' => now()->format('d/m/Y H:i:s')
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les détails de validation de l'étude de profil pour un projet
     */
    public function getDetailsEtudeProfil($projetId): JsonResponse
    {
        try {
            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

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

            if (auth()->user()->profilable->ministere?->id !== $noteConceptuelle->projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer l'évaluation de validation la plus récente
            $evaluation = $projet->evaluations()
                ->where('type_evaluation', 'validation-etude-profil')
                ->whereNotNull('valider_par')
                ->whereNotNull('valider_le')
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'note_conceptuelle' => new $this->resourceClass($noteConceptuelle->load("projet")),
                    'validation' => $evaluation ? [
                        'id' => $evaluation->id,
                        'valider_le' => $evaluation->valider_le ? \Carbon\Carbon::parse($evaluation->valider_le)->format("d/m/Y H:i:s") : null,
                        'valider_par' => new UserResource($evaluation->validator),
                        'decision' => $evaluation->evaluation,
                        'statut' => $evaluation->statut,
                        'commentaire' => $evaluation->commentaire,
                        'historique_evaluations' => EvaluationResource::collection($evaluation->historique_evaluations)
                    ] : null,
                    'rapport' => $projet->rapportFaisabilitePreliminaire()->first() ? new RapportResource($projet->rapportFaisabilitePreliminaire()->first()->load(["historique"])) : null,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la récupération des détails de validation. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
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
            ->whereNotNull('valider_le') // Confirmée
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
     * Traiter la checklist de suivi du rapport de préfaisabilité
     */
    private function traiterChecklistSuiviRapportFaisabilitePreliminaire($rapport, array $checklistData, bool $estBrouillon = false, array $fichiers = []): array
    {
        try {
            DB::beginTransaction();
            /*
            // Récupérer le dernier rapport de préfaisabilité s'il existe
            $rapportExistant = $projet->rapportPrefaisabilite()->first();

            // Déterminer le parent_id pour la hiérarchie (uniquement si soumission finale et rapport existe)
            $parentId = null;
            if ($rapportExistant && !$estBrouillon) {
                $parentId = $rapportExistant->id;
            }

            // Créer le nouveau rapport
            $rapport = \App\Models\Rapport::create([
                'projet_id' => $rapport->projet->id,
                'parent_id' => $parentId,
                'type' => 'prefaisabilite',
                'statut' => $estBrouillon ? 'brouillon' : 'soumis',
                'intitule' => 'Rapport de préfaisabilité - ' . $rapport->projet->titre_projet,
                'checklist_suivi' => $checklistData, // Stocker directement les données
                'info_cabinet_etude' => $fichiers['cabinet_etude'] ?? null,
                'recommandation' => $fichiers['recommandation'] ?? null,
                'date_soumission' => $estBrouillon ? null : now(),
                'soumis_par_id' => $estBrouillon ? null : auth()->id()
            ]);
            */

            // Associer les fichiers au rapport si ils existent
            if (!empty($fichiers)) {
                // Fichier rapport principal
                if (isset($fichiers['rapport'])) {
                    $this->attacherFichierAuRapport($rapport, $fichiers['rapport'], 'rapport');
                }

                // Procès verbal
                if (isset($fichiers['proces_verbal'])) {
                    $this->attacherFichierAuRapport($rapport, $fichiers['proces_verbal'], 'proces-verbal');
                }
            }

            // Traiter les données de checklist via la relation champs() si nécessaire
            $this->traiterChampsChecklistSuivi($rapport, $checklistData);

            DB::commit();

            return [
                'success' => true,
                'message' => $estBrouillon ?
                    'Checklist de suivi sauvegardée en brouillon.' :
                    'Checklist de suivi du rapport de préfaisabilité validée avec succès.',
                'rapport_id' => $rapport->id,
                'projet_id' => $rapport->projet->id,
                'est_brouillon' => $estBrouillon
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement de la checklist de suivi: ' . $e->getMessage()
            ];
        }
    }


    /**
     * Traiter les champs de checklist via la relation champs()
     */
    private function traiterChampsChecklistSuivi($rapport, array $checklistData)
    {
        foreach ($checklistData as $evaluation) {
            $checkpointId   = $evaluation['checkpoint_id'];
            $remarque       = $evaluation['remarque'] ?? null;
            $explication    = $evaluation['explication'] ?? null;

            // Préparer la valeur à stocker (remarque + explication)
            $valeur = $remarque;

            // Créer ou mettre à jour la relation champ-rapport
            $rapport->champs()->syncWithoutDetaching([
                $checkpointId => [
                    'valeur' => $valeur,
                    'commentaire' => $explication,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }

        // Préparer l'évaluation complète pour enregistrement
        $checklist_suivi =  collect($this->documentRepository->getCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire()->all_champs)->map(function ($champ) use ($rapport) {
            $champEvalue = collect($rapport->champs)->firstWhere('attribut', $champ['attribut']);
            return [
                'champ_id'          => $champ['id'],
                'label'             => $champ['label'],
                'attribut'          => $champ['attribut'],
                'ordre_affichage'   => $champ['ordre_affichage'],
                'type_champ'        => $champ['type_champ'],
                'valeur'            => $champEvalue && isset($champEvalue['pivot']['valeur']) ? $champEvalue['pivot']['valeur'] : null,
                'commentaire'       => $champEvalue && isset($champEvalue['pivot']['commentaire']) ? $champEvalue['pivot']['commentaire'] : null,
                'updated_at'        => $champEvalue && isset($champEvalue['pivot']['updated_at']) ? Carbon::parse($champEvalue['pivot']['updated_at'])->format("Y-m-d H:i:s") : null
            ];
        })->toArray();

        $rapport->checklist_suivi = $checklist_suivi;
        $rapport->save();
    }

    /**
     * Déterminer les actions autorisées selon le type de projet (est_dur)
     * et l'état du suivi de la checklist
     */
    private function getActionsAutoriseesSelonTypeProjet(Projet $projet, array $data): array
    {
        $decision = $data['decision'] ?? '';

        // Vérifier si le suivi de la checklist est en cours pour faire_etude_faisabilite_preliminaire
        /*
            if ($decision === 'faire_etude_faisabilite_preliminaire') {
                // Vérifier si le rapport existe et si le suivi est terminé
                $rapport = $projet->rapportFaisabilitePreliminaire()->first();

                // Si le rapport n'existe pas OU si la checklist n'est pas fournie/complète
                if (!$rapport || empty($data['checklist_suivi_rapport_faisabilite_preliminaire'])) {
                    // Le suivi n'est pas terminé, seule l'action 'sauvegarder' est permise
                    return ['sauvegarder'];
                }
            }
        */

        // Actions selon le type de projet
        if ($projet->est_dur) {
            // Projet dur : faire une étude de pré-faisabilité
            return ['abandonner_projet', 'reviser_note_conceptuelle', 'faire_etude_prefaisabilite', 'sauvegarder'];
        } else {
            // Projet mou : peut faire une étude de faisabilité préliminaire
            return ['reviser_note_conceptuelle', 'abandonner_projet', 'sauvegarder', 'faire_etude_faisabilite_preliminaire'];
        }
    }

    /**
     * Traiter automatiquement la décision selon le résultat du contrôle qualité du rapport de faisabilité préliminaire
     */
    private function traiterDecisionEvaluationRapportFaisabilitePrelimAutomatique(Projet $projet, ?array $resultats, $rapport, $evaluation = null): StatutIdee
    {
        if (!$resultats || !isset($resultats['resultat_global'])) {
            throw new Exception("Résultats du contrôle qualité introuvables");
        }

        // Mettre à jour l'évaluation avec les informations de confirmation
        $evaluation->fill([
            'valider_par' => auth()->id(),
            'valider_le' => now(),
            'commentaire' => ($evaluation->commentaire ?? '') . "\n\nCommentaire de confirmation: " . ($data['commentaire_confirmation'] ?? 'Action confirmée pour cas non accepté')
        ]);
        $evaluation->save();

        switch ($resultats['resultat_global']) {
            case 'non_applicable':
            case 'passable':
            case 'passe':
                // Le contrôle qualité a réussi → Passage à MATURITE
                $projet->update([
                    'statut' => StatutIdee::MATURITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::MATURITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::MATURITE),
                    'type_projet' => TypesProjet::simple
                ]);

                if ($rapport) {
                    $rapport->update([
                        'statut' => 'valide',
                        'decision' => 'valider',
                        'commentaire_validation' => $resultats["message_resultat"],
                        'date_validation' => now(),
                        'validateur_id' => auth()->id(),
                        'valider_le' => now()
                    ]);
                }

                return StatutIdee::MATURITE;

            /**
                 *
                 * 'passable' => $nombrePassable,
                 * 'renvoyer' => $nombreRenvoyer,
                 * 'non_accepte' => $nombreNonAccepte,
                 * 'non_applicable' => $nombreNonApplicable,
                 * 'non_completees' => $champsNonCompletes,
                 * 'non_evalues' => $nombreNonEvalues,
                 */
            case 'renvoyer':
            case 'retour':
                // Créer un nouveau rapport en brouillon pour révision
                $rapport->refresh();
                $newRapport = $rapport->replicate();

                $newRapport->statut = 'brouillon';
                $newRapport->parent_id = $rapport->id;
                $newRapport->commentaire_validation = null;
                $newRapport->date_soumission = null;
                $newRapport->created_at = now();
                $newRapport->updated_at = null;
                $newRapport->save();

                // NOTE: L'évaluation du rapport sera créée automatiquement lors de la resoumission
                // La logique de duplication de l'évaluation (champs passés, etc.) sera appliquée à ce moment

                // Retour pour travail supplémentaire → R_VALIDATION_PROFIL_NOTE_AMELIORER
                $projet->update([
                    'statut' => StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER)
                ]);

                $rapport->update([
                    'statut' => 'rejete',
                    'decision' => 'rejete',
                    'commentaire_validation' => $resultats["message_resultat"],
                    'date_validation' => now(),
                    'validateur_id' => auth()->id(),
                    'valider_le' => now()
                ]);

                return StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER;

            case 'non accepté':
            case 'non-accepte':
            case 'non_completees':
            case 'non_accepte':
                // Créer un nouveau rapport en brouillon pour refaire complètement
                $rapport->refresh();
                $newRapport = $rapport->replicate();

                $newRapport->statut = 'brouillon';
                $newRapport->parent_id = $rapport->id;
                $newRapport->commentaire_validation = null;
                $newRapport->date_soumission = null;
                $newRapport->created_at = now();
                $newRapport->updated_at = null;
                $newRapport->save();

                // NOTE: L'évaluation du rapport sera créée automatiquement lors de la resoumission
                // La logique de duplication de l'évaluation (champs passés, etc.) sera appliquée à ce moment

                // Non accepté → R_VALIDATION_PROFIL_NOTE_AMELIORER (révision directe)
                $projet->update([
                    'statut' => StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER)
                ]);

                $rapport->update([
                    'statut' => 'rejete',
                    'decision' => 'rejete',
                    'commentaire_validation' => $resultats["message_resultat"],
                    'date_validation' => now(),
                    'validateur_id' => auth()->id(),
                    'valider_le' => now()
                ]);

                return StatutIdee::VALIDATION_PROFIL;

            default:
                return StatutIdee::VALIDATION_PROFIL;
        }
    }

    /**
     * Créer une nouvelle évaluation basée sur une évaluation parent (pour rapport retourné)
     * Copie les champs "conforme" et "non_applicable" et réinitialise les autres champs
     */
    private function creerEvaluationPourRapportResoumis(Rapport $nouveauRapport, Rapport $ancienRapport): void
    {
        // Récupérer l'évaluation terminée du rapport parent
        $evaluationTerminee = $ancienRapport->evaluations()
            ->where('type_evaluation', 'controle-qualite-rapport-faisabilite-preliminaire')
            ->where('statut', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$evaluationTerminee) {
            return; // Pas d'évaluation à dupliquer
        }

        // Créer une nouvelle évaluation liée au nouveau rapport
        $newEvaluation = $evaluationTerminee->replicate();
        $newEvaluation->projetable_id = $nouveauRapport->id;
        $newEvaluation->projetable_type = get_class($nouveauRapport);
        $newEvaluation->id_evaluation = $evaluationTerminee->id; // Lien vers l'évaluation parent
        $newEvaluation->canevas = $evaluationTerminee->canevas; // Copier le canevas
        $newEvaluation->statut = 0; // En cours
        $newEvaluation->date_debut_evaluation = now();
        $newEvaluation->date_fin_evaluation = null;
        $newEvaluation->valider_le = null;
        $newEvaluation->valider_par = null;
        $newEvaluation->resultats_evaluation = [];

        // Sauvegarder d'abord la nouvelle évaluation avec des valeurs temporaires
        $newEvaluation->evaluation = [];
        $newEvaluation->resultats_evaluation = [];
        $newEvaluation->created_at = now();
        $newEvaluation->updated_at = null;
        $newEvaluation->save();

        // Copier les relations champs_evalue de l'ancienne évaluation
        // Pour les champs "conforme" et "non_applicable" : copier tel quel
        // Pour les autres : mettre null pour forcer la réévaluation
        $champsEvalues = $evaluationTerminee->champs_evalue;
        foreach ($champsEvalues as $champ) {
            $note = $champ->pivot->note;

            if (in_array($note, ['passable', 'non_applicable'])) {
                // Si conforme ou non_applicable, copier tel quel
                $newEvaluation->champs_evalue()->attach($champ->id, [
                    'note' => $note,
                    'date_note' => $champ->pivot->date_note,
                    'commentaires' => $champ->pivot->commentaires,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                // Si non_conforme, mettre null (pas de copie dans pivot)
                // Les anciennes valeurs seront dans le JSON evaluation avec le suffixe "_passer"
            }
        }

        // Recharger pour avoir accès aux relations
        $newEvaluation->refresh();

        // Construire le tableau des évaluations de champs pour le calcul
        $evaluationsChamps = collect($this->documentRepository->getCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire()->all_champs)->map(function ($champ) use ($newEvaluation) {
            $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);

            return [
                'champ_id' => $champ['id'],
                'label' => $champ['label'],
                'attribut' => $champ['attribut'],
                'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
            ];
        })->toArray();

        // Construire le JSON evaluation basé sur les champs copiés
        $resultatsExamen = $this->calculerResultatsControleQualite($nouveauRapport, $newEvaluation);

        // Récupérer l'ancienne évaluation pour référence
        $ancienneEvaluation = $evaluationTerminee->evaluation ?? [];
        $anciensChampsEvalues = collect($ancienneEvaluation['champs_evalues'] ?? []);

        $evaluationComplete = [
            'champs_evalues' => collect($this->documentRepository->getCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire()->all_champs)->map(function ($champ) use ($newEvaluation, $anciensChampsEvalues) {
                $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                $ancienChampEvalue = $anciensChampsEvalues->firstWhere('attribut', $champ['attribut']);

                $result = [
                    'id' => $champEvalue ? $champEvalue['pivot']['id'] : null,
                    'champ_id' => $champ['id'],
                    'label' => $champ['label'],
                    'attribut' => $champ['attribut'],
                    'ordre_affichage' => $champ['ordre_affichage'],
                    'type_champ' => $champ['type_champ'],
                    'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                    'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                    'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                ];

                // Si le champ n'est pas dans la nouvelle évaluation mais existe dans l'ancienne
                // C'est un champ qui n'était pas "passable" ou "non_applicable", on ajoute les anciennes valeurs avec "_passer"
                if (!$champEvalue && $ancienChampEvalue) {
                    $result['appreciation_passer'] = $ancienChampEvalue['appreciation'] ?? null;
                    $result['commentaire_passer_evaluateur'] = $ancienChampEvalue['commentaire_evaluateur'] ?? null;
                    $result['date_appreciation_passer'] = $ancienChampEvalue['date_appreciation'] ?? null;
                }

                return $result;
            })->toArray(),
            'statistiques' => $resultatsExamen
        ];

        // Mettre à jour avec les données complètes
        $newEvaluation->evaluation = $evaluationComplete;
        $newEvaluation->resultats_evaluation = $resultatsExamen;
        $newEvaluation->save();
    }

    /**
     * Soumettre ou resoumettre un rapport de faisabilité préliminaire (SFD-009)
     */
    public function soumettreRapportFaisabilitePreliminaire($projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if (!in_array($projet->statut->value, [StatutIdee::VALIDATION_PROFIL->value, StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de soumission du rapport de faisabilité préliminaire.'
                ], 422);
            }

            // Extraire les données spécifiques au payload
            $estSoumise = $data['est_soumise'] ?? true;

            // Déterminer le statut selon est_soumise
            $statut = $estSoumise ? 'soumis' : 'brouillon';

            // Extraire les données de documents
            $documentsData = $data['documents'] ?? [];

            // Préparer les données du rapport
            $rapportData = [
                'projet_id' => $projetId,
                'type' => 'faisabilite-preliminaire',
                'statut' => $statut,
                'intitule' => $data['intitule'] ?? 'Rapport de faisabilité préliminaire',
                'date_soumission' => $estSoumise ? now() : null,
                'soumis_par_id' => auth()->id(),
            ];

            // Chercher un rapport existant pour ce projet et type
            $rapportExistant = Rapport::where('projet_id', $projetId)
                ->where('type', 'faisabilite-preliminaire')
                ->orderBy("created_at", "desc")
                ->first();

            if ($rapportExistant && $rapportExistant->statut === 'soumis') {
                // Si un rapport soumis existe déjà, créer une nouvelle version avec parent_id
                $rapportData['parent_id'] = $rapportExistant->id;
                $rapport = Rapport::create($rapportData);
                $message = 'Nouvelle version du rapport de faisabilité préliminaire créée avec succès.';
            } elseif ($rapportExistant && in_array($rapportExistant->statut, ['brouillon', 'renvoye', 'non_accepte'])) {
                // Si un rapport en brouillon, renvoyé ou non accepté existe, le mettre à jour
                $rapport = $rapportExistant;
                $rapport->fill($rapportData);
                $rapport->save();
                $message = 'Rapport de faisabilité préliminaire mis à jour avec succès.';
            } else {
                // Créer un nouveau rapport (première version)
                $rapport = Rapport::create($rapportData);
                $message = 'Rapport de faisabilité préliminaire créé avec succès.';
            }

            // Gérer les documents/fichiers du rapport
            if (isset($documentsData["rapport_faisabilite_preliminaire"])) {
                $this->gererFichierRapportFaisabilite($rapport, $documentsData['rapport_faisabilite_preliminaire']);
            }

            if (isset($documentsData["tdr_faisabilite_preliminaire"])) {
                $this->gererFichierRapportFaisabilite($rapport, $documentsData['tdr_faisabilite_preliminaire']);
            }

            if (isset($documentsData["check_suivi_rapport"])) {
                $this->gererFichierRapportFaisabilite($rapport, $documentsData['check_suivi_rapport']);
            }

            // Gérer l'analyse financière et calculer la VAN et le TRI
            if ($estSoumise && isset($data['analyse_financiere'])) {
                $updateData = [];
                $analyseFinanciere = $data['analyse_financiere'];

                $requiredFields = ['duree_vie', 'investissement_initial', 'flux_tresorerie', 'taux_actualisation'];

                foreach ($requiredFields as $field) {
                    // validation de présence de $analyseFinanciere[$field]
                    if (!isset($analyseFinanciere[$field]) && !empty($analyseFinanciere[$field])) {
                        throw ValidationException::withMessages([
                            "analyse_financiere.$field" => "Le champ $field est obligatoire lorsque le projet est financé. " . $analyseFinanciere[$field]
                        ]);
                    }
                    // validations supplémentaires pour les champs spécifiques
                    // Il faut savoir que les donnees sont soumis dans un formdata donc tout est string

                    if ($field === 'duree_vie') {

                        $value = $analyseFinanciere[$field];

                        // Vérifie que c'est bien un nombre ET un entier positif
                        if (!ctype_digit((string)$value) || (int)$value <= 0) {
                            throw ValidationException::withMessages([
                                "analyse_financiere.$field" => "Le champ $field doit être un nombre entier positif (sans virgule)."
                            ]);
                        }

                        // Optionnel : convertir proprement en entier
                        $analyseFinanciere[$field] = (int)$value;
                    }

                    // Ajouter d'autres validations spécifiques si nécessaire
                    if (in_array($field, ['investissement_initial', 'taux_actualisation'])) {
                        if (!is_numeric($analyseFinanciere[$field])) {
                            throw ValidationException::withMessages([
                                "analyse_financiere.$field" => "Le champ $field doit être une date valide au format AAAA-MM-JJ."
                            ]);
                        }

                        // Optionnel : forcer la conversion en float si tu veux l'utiliser ensuite
                        $analyseFinanciere[$field] = (float) $analyseFinanciere[$field];
                    }
                }

                // Préparer les données pour le fill() et la mise à jour
                $financialData = [
                    'duree_vie' => $analyseFinanciere['duree_vie'] ?? $projet->duree_vie,
                    'investissement_initial' => $analyseFinanciere['investissement_initial'] ?? $projet->investissement_initial,
                    'flux_tresorerie' => $analyseFinanciere['flux_tresorerie'] ?? $projet->flux_tresorerie,
                    'taux_actualisation' => $analyseFinanciere['taux_actualisation'] ?? $projet->taux_actualisation,
                ];

                // Mettre à jour le modèle en mémoire avec les nouvelles données financières
                $rapport->fill($financialData);

                // Calculer la VAN et le TRI à partir des données mises à jour
                $van = $rapport->calculerVAN();
                $rapport->van = $van;
                $tri = $rapport->calculerTRI();

                // Ajouter toutes les données financières et les résultats au tableau de mise à jour
                $updateData = array_merge($updateData, $financialData);

                if ($van !== null) {
                    $updateData['van'] = $van;
                }
                if ($tri !== null) {
                    $updateData['tri'] = $tri;
                }

                $rapport->update($updateData);
            }

            // Cas spécifique : Resoumission d'un rapport retourné (R_VALIDATION_PROFIL_NOTE_AMELIORER)
            if ($estSoumise && $projet->statut === StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER) {
                // Si le rapport a un parent, créer une nouvelle évaluation basée sur l'ancienne
                if ($rapport->parent_id) {
                    $ancienRapport = Rapport::find($rapport->parent_id);
                    if ($ancienRapport) {
                        $this->creerEvaluationPourRapportResoumis($rapport, $ancienRapport);
                    }
                }

                // Changer le statut du projet vers VALIDATION_PROFIL
                $projet->update([
                    'statut' => StatutIdee::VALIDATION_PROFIL,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_PROFIL),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_PROFIL)
                ]);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($projet, StatutIdee::VALIDATION_PROFIL);
                $this->enregistrerDecision(
                    $projet,
                    "Resoumission du rapport de faisabilité préliminaire après révision",
                    $data['intitule'] ?? 'Rapport révisé soumis pour réévaluation',
                    auth()->user()->personne->id
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'rapport_id' => $rapport->id,
                    'projet_id' => $projet->id,
                    'statut_rapport' => $rapport->statut,
                    'statut_projet' => $projet->statut->value,
                    'soumis_par' => auth()->id(),
                    'soumis_le' => $estSoumise ? now()->format('d/m/Y H:i:s') : null
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Traiter la décision de validation selon le cas d'utilisation
     */
    private function traiterDecisionValidation($projet, string $decision, array $data, $noteConceptuelle = null, $resultatsControleQualite = null, $rapportFaisabilitePrelim = null, $evaluation = null): \App\Enums\StatutIdee
    {
        switch ($decision) {/*
            case 'projet_a_maturite':
                $projet->update([
                    'statut' => StatutIdee::PRET,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::PRET),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::PRET),
                    'type_projet' => TypesProjet::simple
                ]);
                return StatutIdee::PRET; */

            case 'faire_etude_faisabilite_preliminaire':
                // Traiter selon le résultat du contrôle qualité
                return $this->traiterDecisionEvaluationRapportFaisabilitePrelimAutomatique(
                    $projet,
                    $resultatsControleQualite,
                    $rapportFaisabilitePrelim,
                    $evaluation
                );

            case 'faire_etude_prefaisabilite':
                $projet->update([
                    'statut' => StatutIdee::TDR_PREFAISABILITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                    'type_projet' => TypesProjet::complexe1,
                    'est_dur' => true
                ]);
                return StatutIdee::TDR_PREFAISABILITE;

            case 'reviser_note_conceptuelle':
                // Dupliquer la note conceptuelle comme pour le cas 'retour'
                if ($noteConceptuelle) {
                    $noteConceptuelle->refresh();
                    $newNote = $noteConceptuelle->replicate();

                    $newNote->statut = 0; // Brouillon
                    $newNote->decision = [];
                    $newNote->accept_term = false;
                    $newNote->parentId = $noteConceptuelle->id;
                    $newNote->rediger_par = $noteConceptuelle->redacteur->id;
                    $newNote->created_at = now();
                    $newNote->updated_at = null;

                    // Copier les canevas de la note originale vers la nouvelle note
                    $newNote->canevas_redaction_note_conceptuelle = $noteConceptuelle->canevas_redaction_note_conceptuelle;
                    $newNote->canevas_appreciation_note_conceptuelle = $noteConceptuelle->canevas_appreciation_note_conceptuelle;
                    $newNote->save();

                    // Récupérer l'évaluation terminée de la note conceptuelle
                    $evaluation = $noteConceptuelle->evaluationTermine();

                    if ($evaluation) {
                        // Créer une nouvelle évaluation liée à la nouvelle note avec les données de l'ancienne
                        $newEvaluation = $evaluation->replicate();
                        $newEvaluation->projetable_id = $newNote->id;
                        $newEvaluation->projetable_type = get_class($newNote);
                        $newEvaluation->id_evaluation = $evaluation->id; // Lien vers l'évaluation parent
                        $newEvaluation->canevas = $evaluation->canevas; // Copier le canevas
                        $newEvaluation->statut = 0; // En cours
                        $newEvaluation->date_debut_evaluation = now();
                        $newEvaluation->date_fin_evaluation = null;
                        $newEvaluation->valider_le = null;
                        $newEvaluation->valider_par = null;
                        $newEvaluation->resultats_evaluation = [];

                        // Sauvegarder d'abord la nouvelle évaluation avec des valeurs temporaires
                        $newEvaluation->evaluation = [];
                        $newEvaluation->resultats_evaluation = [];
                        $newEvaluation->created_at = now();
                        $newEvaluation->updated_at = null;
                        $newEvaluation->save();

                        // Copier les relations champs_evalue de l'ancienne évaluation
                        // Pour les champs "passé" : copier tel quel
                        // Pour les autres (retour/non_accepte) : mettre null pour forcer la réévaluation
                        $champsEvalues = $evaluation->champs_evalue;
                        foreach ($champsEvalues as $champ) {
                            $note = $champ->pivot->note;

                            if ($note === 'passe') {
                                // Si passé, copier tel quel
                                $newEvaluation->champs_evalue()->attach($champ->id, [
                                    'note' => $note,
                                    'date_note' => $champ->pivot->date_note,
                                    'commentaires' => $champ->pivot->commentaires,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            } else {
                                // Si retour ou non_accepte, mettre null (pas de copie dans pivot)
                                // Les anciennes valeurs seront dans le JSON evaluation avec le suffixe "_passer"
                            }
                        }

                        // Recharger pour avoir accès aux relations
                        $newEvaluation->refresh();

                        // Construire le JSON evaluation basé sur les champs copiés
                        $resultatsExamen = $this->calculerResultatsExamen($newNote, $newEvaluation);

                        // Récupérer l'ancienne évaluation pour référence
                        $ancienneEvaluation = $evaluation->evaluation ?? [];
                        $anciensChampsEvalues = collect($ancienneEvaluation['champs_evalues'] ?? []);

                        $evaluationComplete = [
                            'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($newEvaluation, $anciensChampsEvalues) {
                                $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                                $ancienChampEvalue = $anciensChampsEvalues->firstWhere('attribut', $champ['attribut']);

                                $result = [
                                    'id' => $champEvalue ? $champEvalue['pivot']['id'] : null,
                                    'champ_id' => $champ['id'],
                                    'label' => $champ['label'],
                                    'attribut' => $champ['attribut'],
                                    'ordre_affichage' => $champ['ordre_affichage'],
                                    'type_champ' => $champ['type_champ'],
                                    'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                                    'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                                    'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                                ];

                                // Si le champ n'est pas dans la nouvelle évaluation mais existe dans l'ancienne
                                // C'est un champ qui n'était pas "passé", on ajoute les anciennes valeurs avec "_passer"
                                if (!$champEvalue && $ancienChampEvalue) {
                                    $result['appreciation_passer'] = $ancienChampEvalue['appreciation'] ?? null;
                                    $result['commentaire_passer_evaluateur'] = $ancienChampEvalue['commentaire_evaluateur'] ?? null;
                                    $result['date_appreciation_passer'] = $ancienChampEvalue['date_appreciation'] ?? null;
                                }

                                return $result;
                            })->toArray(),
                            'statistiques' => $resultatsExamen
                        ];

                        // Mettre à jour avec les données complètes
                        $newEvaluation->evaluation = $evaluationComplete;
                        $newEvaluation->resultats_evaluation = $resultatsExamen;
                        $newEvaluation->save();
                    }
                }

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
    /**
     * Traiter la checklist de suivi de l'étude de profil (faisabilité préliminaire)
     */
    private function traiterChecklistSuiviEtudeProfil($projet, array $checklistData): array
    {
        try {
            // Préparer l'évaluation complète pour enregistrement
            $checklist_suivi = collect($this->documentRepository->getCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire()->all_champs)->map(function ($champ) use ($checklistData) {
                // Trouver l'évaluation correspondante dans les données soumises
                $evaluation = collect($checklistData)->firstWhere('checkpoint_id', $champ['id']);

                return [
                    'champ_id'          => $champ['id'],
                    'label'             => $champ['label'],
                    'attribut'          => $champ['attribut'],
                    'ordre_affichage'   => $champ['ordre_affichage'],
                    'type_champ'        => $champ['type_champ'],
                    'remarque'          => $evaluation['remarque'] ?? null,
                    'explication'       => $evaluation['explication'] ?? null,
                    'updated_at'        => now()->format("Y-m-d H:i:s")
                ];
            })->toArray();

            return $checklist_suivi;
        } catch (\Exception $e) {
            \Log::error('Erreur lors du traitement de la checklist de suivi de l\'étude de profil', [
                'error' => $e->getMessage(),
                'projet_id' => $projet->id
            ]);
            return [];
        }
    }

    private function envoyerNotificationValidation($projet, string $decision, string $commentaire): void
    {
        try {
            $typeNotification = match ($decision) {
                //'projet_a_maturite' => 'projet_pret',
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
        return match ($decision) {
            //'projet_a_maturite' => 'Projet validé comme prêt pour la suite.',
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
        return match ($decision) {/*
            'projet_a_maturite' => [
                'statut_change' => 'Statut changé vers "Prêt"',
                'type_projet' => 'Type défini comme "Simple"',
                'notification' => 'Notification envoyée'
            ], */
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
            'observateur_id' => auth()->user()->id,
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
            \App\Enums\StatutIdee::EVALUATION_NOTE => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::R_VALIDATION_NOTE_AMELIORER => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER => \App\Enums\PhasesIdee::identification,

            \App\Enums\StatutIdee::VALIDATION_PROFIL => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::VALIDATION_NOTE_AMELIORER => \App\Enums\PhasesIdee::identification,
            \App\Enums\StatutIdee::MATURITE => \App\Enums\PhasesIdee::evaluation_ex_tante,
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
            \App\Enums\StatutIdee::EVALUATION_NOTE => \App\Enums\SousPhaseIdee::etude_de_profil,
            \App\Enums\StatutIdee::R_VALIDATION_NOTE_AMELIORER => \App\Enums\SousPhaseIdee::etude_de_profil,
            \App\Enums\StatutIdee::VALIDATION_PROFIL => \App\Enums\SousPhaseIdee::etude_de_profil,
            \App\Enums\StatutIdee::VALIDATION_NOTE_AMELIORER => \App\Enums\SousPhaseIdee::etude_de_profil,
            \App\Enums\StatutIdee::R_VALIDATION_PROFIL_NOTE_AMELIORER => \App\Enums\SousPhaseIdee::etude_de_profil,

            StatutIdee::MATURITE => \App\Enums\SousPhaseIdee::redaction_rapport_evaluation_ex_ante,
            default => \App\Enums\SousPhaseIdee::etude_de_profil,
        };
    }

    /**
     * Gérer les documents/fichiers associés à la note conceptuelle avec FichierRepository
     */
    private function handleDocumentsWithFichierRepository(NoteConceptuelle $noteConceptuelle, array $documentsData): void
    {
        foreach ($documentsData as $category => $files) {
            // Récupérer les fichiers existants pour cette catégorie
            $existingFiles = $this->getExistingFilesForCategory($noteConceptuelle, $category);

            // Si aucun nouveau fichier fourni → on ne fait rien
            if (empty($files)) {
                continue;
            }

            $newFilesAdded = false;

            // Cas 1 : catégorie avec plusieurs fichiers (ex: "autres")
            if (is_array($files)) {
                foreach ($files as $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $this->storeDocumentWithFichierRepository($noteConceptuelle, $file, $category);
                        $newFilesAdded = true;
                    }
                }
            }
            // Cas 2 : catégorie avec un seul fichier
            elseif ($files instanceof \Illuminate\Http\UploadedFile) {
                $this->storeDocumentWithFichierRepository($noteConceptuelle, $files, $category);
                $newFilesAdded = true;
            }

            // Supprimer uniquement si on a ajouté de nouveaux fichiers
            if ($newFilesAdded && !empty($existingFiles)) {
                $this->removeSpecificFiles($existingFiles);
            }
        }
    }

    /**
     * Stocker un document/fichier avec FichierRepository
     */
    private function storeDocumentWithFichierRepository(NoteConceptuelle $noteConceptuelle, $file, string $category): void
    {
        if ($file === null || $file === 'null') return;

        // Hasher l'identifiant BIP pour le stockage physique
        $hashedIdentifiantBip = hash('sha256', $noteConceptuelle->projet->identifiant_bip);
        $hashedNoteId = hash('sha256', $noteConceptuelle->id);

        // Générer un nom unique pour le fichier
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storageName = $this->generateStorageName($category, $noteConceptuelle->id, $extension);

        // Stocker le fichier selon la nouvelle structure
        $storedPath = $file->storeAs(
            "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle/{$hashedNoteId}",
            $storageName,
            'local'
        );

        // Créer ou récupérer la structure de dossiers pour note conceptuelle
        $dossierNoteConceptuelle = $this->getOrCreateNoteConceptuelleFolderStructure($noteConceptuelle->projetId, 'note-conceptuelle');

        // Générer le hash d'accès public
        $hashAcces = $this->generateFileAccessHash($noteConceptuelle->id, $storageName, $category);

        // Préparer les données pour FichierRepository
        $fichierData = [
            'nom_original' => $originalName,
            'nom_stockage' => $storageName,
            'chemin' => $storedPath,
            'extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'taille' => $file->getSize(),
            'hash_md5' => md5_file($file->getRealPath()),
            'hash_acces' => $hashAcces,
            'description' => $this->getDescriptionByCategory($category),
            'commentaire' => null,
            'dossier_id' => $dossierNoteConceptuelle?->id,
            'metadata' => [
                'type_document' => 'note-conceptuelle-' . str_replace('_', '-', $category),
                'note_conceptuelle_id' => $noteConceptuelle->id,
                'projet_id' => $noteConceptuelle->projetId,
                'categorie_originale' => $category,
                'soumis_par' => auth()->id(),
                'soumis_le' => now(),
                'dossier_public' => $dossierNoteConceptuelle ? $dossierNoteConceptuelle->full_path : 'Projets/' . $hashedIdentifiantBip . '/Evaluation ex-ante/Etude de profil/Note conceptuelle' . ($category != 'autres_documents' || $category != 'autres-documents' ? '' : ('/Autres documents')),
            ],
            'fichier_attachable_id' => $noteConceptuelle->id,
            'fichier_attachable_type' => NoteConceptuelle::class,
            'categorie' => $category,
            'ordre' => $this->getOrderByCategory($category),
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ];

        // Utiliser FichierRepository pour créer le fichier
        $this->fichierRepository->create($fichierData);
    }

    /**
     * Gérer les documents/fichiers associés à la note conceptuelle
     */
    private function handleDocuments(NoteConceptuelle $noteConceptuelle, array $documentsData): void
    {
        foreach ($documentsData as $category => $files) {
            if (is_array($files)) {
                // Pour les documents multiples (ex: autres)
                foreach ($files as $file) {
                    if ($file) {
                        $this->storeDocument($noteConceptuelle, $file, $category);
                    }
                }
            } else {
                // Pour un seul document
                if ($files) {
                    $this->storeDocument($noteConceptuelle, $files, $category);
                }
            }
        }
    }

    /**
     * Stocker un document/fichier
     */
    private function storeDocument(NoteConceptuelle $noteConceptuelle, $file, string $category): void
    {
        // Générer un nom unique pour le fichier
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storageName = $this->generateStorageName($category, $noteConceptuelle->id, $extension);

        // Utiliser identifiantBip pour le chemin selon le pattern projets/{identifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle/{hash_id}
        $identifiantBip = $noteConceptuelle->projet->identifiantBip;
        $hashedNoteId = hash('sha256', $noteConceptuelle->id);

        // Stocker le fichier selon la nouvelle structure
        $storedPath = $file->storeAs(
            "projets/{$identifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle/{$hashedNoteId}",
            $storageName,
            'local'
        );

        // Déterminer la description selon la catégorie
        $description = $this->getDescriptionByCategory($category);

        // Générer le hash d'accès public
        $hashAcces = $this->generateFileAccessHash($noteConceptuelle->id, $storageName, $category);

        // Créer l'entrée dans la table fichiers
        $noteConceptuelle->fichiers()->create([
            'nom_original' => $originalName,
            'nom_stockage' => $storageName,
            'chemin' => $storedPath,
            'extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'taille' => $file->getSize(),
            'hash_md5' => md5_file($file->getRealPath()),
            'hash_acces' => $hashAcces,
            'description' => $description,
            'commentaire' => null,
            'metadata' => [
                'type_document' => 'note-conceptuelle-' . str_replace('_', '-', $category),
                'note_conceptuelle_id' => $noteConceptuelle->id,
                'projet_id' => $noteConceptuelle->projetId,
                'categorie_originale' => $category,
                'soumis_par' => auth()->id(),
                'soumis_le' => now(),
                'folder_structure' => "projets/{$identifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle/{$hashedNoteId}"
            ],
            'fichier_attachable_id' => $noteConceptuelle->id,
            'fichier_attachable_type' => NoteConceptuelle::class,
            'categorie' => $category,
            'ordre' => $this->getOrderByCategory($category),
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);
    }

    private function storeFile(NoteConceptuelle $noteConceptuelle, $file, string $category): void
    {
        // Générer un nom unique pour le fichier
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storageName = $this->generateStorageName($category, $noteConceptuelle->id, $extension);

        // Utiliser identifiantBip pour le chemin selon le pattern projets/{identifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle/{hash_id}
        $identifiantBip = $noteConceptuelle->projet->identifiantBip;
        $hashedNoteId = hash('sha256', $noteConceptuelle->id);

        // Stocker le fichier selon la nouvelle structure
        $storedPath = $file->storeAs(
            "projets/{$identifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle/{$hashedNoteId}",
            $storageName,
            'local' // disque local sécurisé
        );

        // Déterminer la description selon la catégorie
        $description = $this->getDescriptionByCategory($category);

        // Générer le hash d'accès public
        $hashAcces = $this->generateFileAccessHash($noteConceptuelle->id, $storageName, $category);

        // Créer l'entrée dans la table fichiers
        $noteConceptuelle->fichiers()->create([
            'nom_original' => $originalName,
            'nom_stockage' => $storageName,
            'chemin' => $storedPath,
            'extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'taille' => $file->getSize(),
            'hash_md5' => md5_file($file->getRealPath()),
            'hash_acces' => $hashAcces,
            'description' => $description,
            'commentaire' => null,
            'metadata' => [
                'type_document' => 'note-conceptuelle-' . str_replace('_', '-', $category),
                'note_conceptuelle_id' => $noteConceptuelle->id,
                'projet_id' => $noteConceptuelle->projetId,
                'categorie_originale' => $category,
                'soumis_par' => auth()->id(),
                'soumis_le' => now(),
                'folder_structure' => "projets/{$identifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle/{$hashedNoteId}"
            ],
            'fichier_attachable_id' => $noteConceptuelle->id,
            'fichier_attachable_type' => NoteConceptuelle::class,
            'categorie' => $category,
            'ordre' => $this->getOrderByCategory($category),
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);
    }


    /**
     * Générer un nom de stockage selon la catégorie
     */
    private function generateStorageName(string $category, int $noteConceptuelleId, string $extension): string
    {
        $prefix = match ($category) {
            'analyse_pre_risque_facteurs_reussite' => 'analyse_pre_risque',
            'etude_pre_faisabilite' => 'etude_pre_faisabilite',
            'note_conceptuelle' => 'note_conceptuelle',
            'autres' => 'autres_documents',
            default => $category
        };

        return $prefix . '_' . $noteConceptuelleId . '_' . time() . '.' . $extension;
    }

    /**
     * Obtenir la description selon la catégorie de document
     */
    private function getDescriptionByCategory(string $category): string
    {
        return match ($category) {
            'analyse_pre_risque_facteurs_reussite' => 'Analyse pré-risque et facteurs de réussite',
            'etude_pre_faisabilite' => 'Étude de pré-faisabilité',
            'note_conceptuelle' => 'Note conceptuelle',
            'autres' => 'Autres documents',
            default => ucfirst(str_replace('_', ' ', $category))
        };
    }

    /**
     * Obtenir l'ordre d'affichage selon la catégorie
     */
    private function getOrderByCategory(string $category): int
    {
        return match ($category) {
            'note_conceptuelle' => 1,
            'analyse_pre_risque_facteurs_reussite' => 2,
            'etude_pre_faisabilite' => 3,
            'autres' => 4,
            default => 99
        };
    }

    /**
     * Générer un hash d'accès unique pour un fichier
     */
    private function generateFileAccessHash(int $noteConceptuelleId, string $storageName, string $category): string
    {
        $data = [
            'note_conceptuelle_id' => $noteConceptuelleId,
            'storage_name' => $storageName,
            'category' => $category,
            'timestamp' => time(),
            'salt' => config('app.key', 'default_salt')
        ];

        return hash('sha256', json_encode($data));
    }

    /**
     * Récupérer les fichiers existants pour une catégorie donnée
     */
    private function getExistingFilesForCategory(NoteConceptuelle $noteConceptuelle, string $category)
    {
        return $this->fichierRepository->getInstance()
            ->where('fichier_attachable_id', $noteConceptuelle->id)
            ->where('fichier_attachable_type', NoteConceptuelle::class)
            ->where('categorie', $category)
            ->get();
    }

    /**
     * Supprimer une liste spécifique de fichiers ou un seul fichier
     *
     * @param \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|array|\App\Models\Fichier $files
     * @return void
     */
    private function removeSpecificFiles($files): void
    {
        // Normaliser l'entrée en tableau
        if (!is_array($files) && !($files instanceof \Illuminate\Support\Collection)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            // Vérifier que c'est un objet Fichier valide
            if (!$file || !isset($file->chemin)) {
                continue;
            }

            // Supprimer le fichier physique du storage
            if (Storage::disk('local')->exists($file->chemin)) {
                Storage::disk('local')->delete($file->chemin);
            }

            // Supprimer l'enregistrement de la base de données
            if (isset($file->id)) {
                $this->fichierRepository->delete($file->id);
            }
        }
    }

    /**
     * Créer ou récupérer la structure de dossiers pour les notes conceptuelle de l'etude de profil
     */
    private function getOrCreateNoteConceptuelleFolderStructure(int $projetId, string $type = 'note-conceptuelle'): ?Dossier
    {
        try {
            // Récupérer le projet pour avoir l'identifiant BIP
            $projet = \App\Models\Projet::find($projetId);
            if (!$projet) {
                return null;
            }

            // 1. Dossier racine : "Projets"
            $dossierRacine = Dossier::firstOrCreate([
                'nom' => 'Projets',
                'parent_id' => null
            ], [
                'nom' => 'Projets',
                'description' => 'Dossier principal contenant tous les projets BIP',
                'parent_id' => null,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#2563EB',
                'icone' => 'collection'
            ]);

            // 2. Sous-dossier : Identifiant BIP du projet
            $dossierProjet = Dossier::firstOrCreate([
                'nom' => $projet->identifiant_bip,
                'parent_id' => $dossierRacine->id
            ], [
                'nom' => $projet->identifiant_bip,
                'description' => 'Documents du projet ' . $projet->identifiant_bip,
                'parent_id' => $dossierRacine->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#059669',
                'icone' => 'folder'
            ]);

            // 3. Sous-dossier : "Evaluation ex-ante"
            $dossierEvaluation = Dossier::firstOrCreate([
                'nom' => 'Evaluation ex-ante',
                'parent_id' => $dossierProjet->id
            ], [
                'nom' => 'Evaluation ex-ante',
                'description' => 'Documents d\'évaluation ex-ante du projet',
                'parent_id' => $dossierProjet->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#7C3AED',
                'icone' => 'chart-pie'
            ]);

            // 4. Sous-dossier : "Etude de profil"
            $dossierEtude = Dossier::firstOrCreate([
                'nom' => 'Etude de profil',
                'parent_id' => $dossierEvaluation->id
            ], [
                'nom' => 'Etude de profil',
                'description' => 'Documents de l\'étude de profil',
                'parent_id' => $dossierEvaluation->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#DC2626',
                'icone' => 'document-text'
            ]);

            // 5. Sous-dossier : "Note conceptuelle"
            $dossierNoteConceptuelle = Dossier::firstOrCreate([
                'nom' => 'Note conceptuelle',
                'parent_id' => $dossierEtude->id
            ], [
                'nom' => 'Note conceptuelle',
                'description' => 'Note conceptuelle pour l\'étude de profil',
                'parent_id' => $dossierEtude->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#F59E0B',
                'icone' => 'clipboard-list'
            ]);
            if ($type != "autres-documents" || $type != "autres_documents") {
                return $dossierNoteConceptuelle;
            }

            // 6. Sous-dossier selon le type
            $nomSousDossier = match ($type) {
                'autres-documents' => 'Autres documents',
                'autres_documents' => 'Autres documents',
                'note-conceptuelle' => 'Documents de la note conceptuelle',
                'rapports' => 'Rapports',
                default => 'Documents de la note conceptuelle'
            };

            $descriptionSousDossier = match ($type) {
                'autres-documents' => 'Autres documents annexes a la note conceptuelle',
                'autres_documents' => 'Autres documents annexes a la note conceptuelle',
                'note-conceptuelle' => 'Documents des termes de référence',
                'rapports' => 'Rapports d\'étude de profil',
                default => 'Documents des termes de référence'
            };

            $couleurSousDossier = match ($type) {
                'autres-documents' => '#6B7280',
                'autres_documents' => '#6B7280',
                'note-conceptuelle' => '#10B981',
                'rapports' => '#EF4444',
                default => '#10B981'
            };

            $iconeSousDossier = match ($type) {
                'autres-documents' => 'document-duplicate',
                'autres_documents' => 'document-duplicate',
                'note-conceptuelle' => 'document-text',
                'rapports' => 'document-report',
                default => 'document-text'
            };

            $sousDossierFinal = Dossier::firstOrCreate([
                'nom' => $nomSousDossier,
                'parent_id' => $dossierNoteConceptuelle->id
            ], [
                'nom' => $nomSousDossier,
                'description' => $descriptionSousDossier,
                'parent_id' => $dossierNoteConceptuelle->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => $couleurSousDossier,
                'icone' => $iconeSousDossier
            ]);

            return $sousDossierFinal;
        } catch (\Exception $e) {
            // En cas d'erreur, retourner null et laisser le fichier sans dossier
            \Log::warning('Erreur lors de la création de la structure de dossiers TDR', [
                'error' => $e->getMessage(),
                'projet_id' => $projetId,
                'type' => $type
            ]);
            return null;
        }
    }


    /**
     * Gérer le fichier rapport avec versioning intelligent
     */
    private function gererFichierRapportFaisabilite(Rapport $rapport, $fichier): ?Fichier
    {
        // Calculer le hash du nouveau fichier
        $nouveauHash = md5_file($fichier->getRealPath());

        // Vérifier s'il y a déjà un fichier rapport avec le même hash lié à ce rapport
        $fichierIdentique = $rapport->fichiersRapport()
            ->where('hash_md5', $nouveauHash)
            ->where('is_active', true)
            ->first();

        if ($fichierIdentique) {
            return $fichierIdentique;
        }

        // Désactiver les anciens fichiers rapport de ce rapport
        $rapport->fichiersRapport()
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Hasher les IDs pour le chemin selon le pattern projets/{hash_projet_id}/etude_de_faisabilite/rapport/{hash_id}
        $hashedProjectId = hash('sha256', $rapport->projet->identifiant_bip);
        $hashedRapportId = hash('sha256', $rapport->id);

        // Stocker le fichier sur le disque
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $nomOriginal;
        $chemin = $fichier->storeAs("projets/{$hashedProjectId}/evaluation_ex_ante/etude_de_faisabilite/rapport_faisabilite/{$hashedRapportId}", $nomStockage, 'local');

        // Créer le nouveau fichier et l'associer au rapport
        $fichierCree = Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => $nouveauHash,
            'description' => 'Rapport de faisabilité',
            'categorie' => 'rapport',
            'is_active' => true,
            'uploaded_by' => auth()->id(),
            'metadata' => [
                'type_document' => 'rapport-faisabilite',
                'rapport_id' => $rapport->id,
                'projet_id' => $rapport->projet_id,
                'statut' => 'actif',
                'soumis_par' => auth()->id(),
                'soumis_le' => now(),
                'folder_structure' => "projets/{$hashedProjectId}/evaluation_ex_ante/etude_de_faisabilite/rapport_faisabilite/{$hashedRapportId}"
            ],
            'fichier_attachable_type' => Rapport::class,
            'fichier_attachable_id' => $rapport->id
        ]);



        // Supprimer les anciens fichiers maintenant que les nouveaux sont enregistrés
        $this->removeSpecificFiles($fichierIdentique);

        return $fichierCree;
    }
}
