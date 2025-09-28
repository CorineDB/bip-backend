<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Models\Fichier;
use App\Models\Projet;
use App\Models\Rapport;
use App\Models\Decision;
use App\Models\Workflow;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use App\Http\Resources\TdrResource;
use App\Http\Resources\projets\ProjetResource;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\CategorieCritereRepositoryInterface;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\TdrRepositoryInterface;
use App\Services\Contracts\TdrFaisabiliteServiceInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Helpers\SlugHelper;
use App\Http\Resources\FichierResource;
use App\Http\Resources\RapportResource;
use App\Models\Dgpd;
use App\Models\Dossier;
use App\Models\Tdr;
use App\Repositories\Contracts\FichierRepositoryInterface;
use Illuminate\Validation\ValidationException;

class TdrFaisabiliteService extends BaseService implements TdrFaisabiliteServiceInterface
{
    protected TdrRepositoryInterface $tdrRepository;
    protected DocumentRepositoryInterface $documentRepository;
    protected ProjetRepositoryInterface $projetRepository;
    protected EvaluationRepositoryInterface $evaluationRepository;
    protected CategorieCritereRepositoryInterface $categorieCritereRepository;
    protected FichierRepositoryInterface $fichierRepository;

    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        ProjetRepositoryInterface $projetRepository,
        EvaluationRepositoryInterface $evaluationRepository,
        CategorieCritereRepositoryInterface $categorieCritereRepository,
        TdrRepositoryInterface $tdrRepository,
        FichierRepositoryInterface $fichierRepository
    ) {
        parent::__construct($tdrRepository);

        $this->documentRepository = $documentRepository;
        $this->projetRepository = $projetRepository;
        $this->evaluationRepository = $evaluationRepository;
        $this->categorieCritereRepository = $categorieCritereRepository;
        $this->tdrRepository = $tdrRepository;
        $this->fichierRepository = $fichierRepository;
    }

    protected function getResourceClass(): string
    {
        return ProjetResource::class;
    }

    protected function getResourcesClass(): string
    {
        return ProjetResource::class;
    }

    /**
     * Créer un nouveau TDR de faisabilité
     */
    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Créer le TDR
            $tdr = $this->repository->create($data);

            DB::commit();

            return (new TdrResource($tdr->load(['soumisPar', 'redigerPar', 'fichiers.uploadedBy'])))
                ->additional(['message' => 'TDR de faisabilité créé avec succès.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Mettre à jour un TDR de faisabilité
     */
    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $tdr = $this->repository->findOrFail($id);
            $tdr->update($data);

            DB::commit();

            return (new TdrResource($tdr->load(['soumisPar', 'redigerPar', 'fichiers.uploadedBy'])))
                ->additional(['message' => 'TDR de faisabilité mis à jour avec succès.'])
                ->response();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les détails des TDRs de faisabilité soumis
     */
    public function getTdrDetails(int $projetId): JsonResponse
    {
        try {
            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifications des droits d'accès (identique à faisabilité)
            if (auth()->user()->profilable->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer le TDR le plus récent pour ce projet
            $tdr = $this->tdrRepository->getModel()
                ->where('projet_id', $projetId)
                ->where('type', 'faisabilite')
                ->with(['soumisPar', 'redigerPar', 'fichiers.uploadedBy'])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$tdr) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucun TDR de faisabilité trouvé pour ce projet.'
                ], 206);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tdr' => new TdrResource($tdr->load("projet")),
                    'statut_projet' => $projet->statut
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la récupération des détails du TDR. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }

    /**
     * Soumettre les TDRs de faisabilité (SFD-014)
     */
    public function soumettreTdrs(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifications des permissions avancées (similaire à faisabilité)
            if (!auth()->user()->hasPermissionTo('soumettre-un-tdr-de-faisabilite') && auth()->user()->type !== 'dpaf' && auth()->user()->profilable_type !== 'App\\Models\\Dpaf') {
                throw new Exception("Vous n'avez pas les droits d'accès pour effectuer cette action", 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if (!in_array($projet->statut->value, [StatutIdee::TDR_FAISABILITE->value, StatutIdee::R_TDR_FAISABILITE->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de soumission des TDRs de faisabilité.'
                ], 422);
            }

            // Extraire les données spécifiques au payload
            $estSoumise = $data['est_soumise'] ?? true;

            // Déterminer le statut selon est_soumise
            $statut = $estSoumise ? 'soumis' : 'brouillon';

            // Extraire les données spécifiques au payload
            //$champsData = $data['champs'] ?? [];
            $documentsData = $data['autres_document'] ?? [];

            // Préparer les données du TDR
            $tdrData = [
                'projet_id' => $projetId,
                'type' => 'faisabilite',
                'statut' => $statut,
                'resume' => $data['resume_tdr_faisabilite'] ?? 'TDR de faisabilité',
                'termes_de_reference' => [],
                'date_soumission' => $estSoumise ? now() : null,
                'soumis_par_id' => auth()->id(),
                'rediger_par_id' => auth()->id(),
            ];

            // Chercher un TDR existant pour ce projet et type
            $tdrExistant = \App\Models\Tdr::where('projet_id', $projetId)
                ->where('type', 'faisabilite')
                ->orderBy("created_at", "desc")
                ->first();

            if ($tdrExistant && $tdrExistant->statut === 'soumis') {
                // Si un TDR soumis existe déjà, créer une nouvelle version avec parent_id
                $tdrData['parent_id'] = $tdrExistant->id;
                $tdr = \App\Models\Tdr::create($tdrData);
                $message = 'Nouvelle version du TDR de faisabilité créée avec succès.';
            } elseif ($tdrExistant && ($tdrExistant->statut === 'brouillon' || $tdrExistant->statut === 'retour_travail_supplementaire')) {
                // Si un TDR en brouillon ou en retour de travail supplémentaire existe, le mettre à jour
                $tdr = $tdrExistant;
                $tdr->fill($tdrData);
                $tdr->save();
                $message = 'TDR de faisabilité mis à jour avec succès.';
            } else {
                // Créer un nouveau TDR (première version)
                $tdr = \App\Models\Tdr::create($tdrData);
                $message = 'TDR de faisabilité créé avec succès.';
            }

            // Gérer les documents/fichiers
            if (!empty($documentsData)) {
                $this->handleDocuments($tdr, $documentsData);
            }

            // Traitement et sauvegarde du fichier TDR (legacy)
            $fichierTdr = null;
            if (isset($data['tdr'])) {
                $fichierTdr = $this->sauvegarderFichierTdr($tdr, $data['tdr'], $data);
            }

            $projet->resume_tdr_faisabilite = $data["resume_tdr_faisabilite"];

            // Changer le statut du projet seulement si est_soumise est true
            if ($estSoumise) {
                $projet->update([
                    'statut' => StatutIdee::EVALUATION_TDR_F,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::EVALUATION_TDR_F),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::EVALUATION_TDR_F)
                ]);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($projet, StatutIdee::EVALUATION_TDR_F);
                $this->enregistrerDecision(
                    $projet,
                    "Soumission des TDRs de faisabilité",
                    $data['resume_tdr_faisabilite'] ?? 'TDRs soumis pour évaluation',
                    auth()->user()->personne->id
                );

                // Envoyer une notification
                $this->envoyerNotificationSoumission($projet, $fichierTdr);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'tdr' => new TdrResource($tdr),
                    'fichier_id' => $fichierTdr ? $fichierTdr->id : null,
                    'projet_id' => $projet->id,
                    'ancien_statut' => in_array($projet->statut->value, [StatutIdee::TDR_FAISABILITE->value, StatutIdee::R_TDR_FAISABILITE->value]) ? $projet->statut->value : StatutIdee::TDR_FAISABILITE->value,
                    'nouveau_statut' => $estSoumise ? StatutIdee::EVALUATION_TDR_F->value : $projet->statut->value,
                    'fichier_url' => $fichierTdr ? $fichierTdr->url : null,
                    'resume' => $data['resume'] ?? null,
                    'tdr_faisabilite' => $data['tdr_faisabilite'] ?? null,
                    'type_tdr' => $data['type_tdr'] ?? null,
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
     * Apprécier et évaluer les TDRs de faisabilité (SFD-015)
     */
    public function evaluerTdrs(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DGPD uniquement)
            if (!auth()->user()->hasPermissionTo('apprecier-un-tdr-de-faisabilite') && auth()->user()->type !== 'dgpd' && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n\'avez pas les droits pour effectuer cette évaluation.", 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if (!in_array($projet->statut->value, [StatutIdee::EVALUATION_TDR_F->value, StatutIdee::R_TDR_FAISABILITE->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape d\'évaluation des TDRs.'
                ], 422);
            }

            // Récupérer le TDR de faisabilité à évaluer
            $tdr = \App\Models\Tdr::where('projet_id', $projetId)
                ->where('type', 'faisabilite')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$tdr) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun TDR de faisabilité trouvé pour ce projet.'
                ], 404);
            }

            // Vérifier que le TDR est soumis et peut être évalué
            if (!$tdr->peutEtreEvalue()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le TDR doit être soumis avant de pouvoir être évalué.'
                ], 422);
            }

            $tdr->statut = 'en_evaluation';
            $tdr->save();
            $tdr->refresh();

            // Créer ou mettre à jour l'évaluation
            $evaluation = $this->creerEvaluationTdr($tdr, $data);

            // Calculer le résultat de l'évaluation selon les règles SFD-015
            $resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, $data);

            // Traiter la décision selon le résultat (changement automatique du statut)
            $nouveauStatut = $this->traiterDecisionEvaluationTdrAutomatique($projet, $resultatsEvaluation, $tdr);

            // Préparer l'évaluation complète pour enregistrement
            $evaluationComplete = [
                'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationTdrFaisabilite()->all_champs)->map(function ($champ) use ($evaluation) {
                    $champEvalue = collect($evaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                    return [
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
                'confirme_par' => new UserResource(auth()->user())
            ];

            // Mettre à jour l'évaluation avec les données complètes
            $evaluation->fill([
                'resultats_evaluation' => $resultatsEvaluation,
                'evaluation' => json_encode($evaluationComplete),
                'valider_par' => auth()->id(),
                'valider_le' => now(),
                'commentaire' => $resultatsEvaluation['message_resultat']
            ]);

            $evaluation->save();

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, $nouveauStatut);
            $this->enregistrerDecision(
                $projet,
                "Évaluation des TDRs de faisabilité - " . ucfirst($resultatsEvaluation['resultat_global']),
                $data['commentaire'] ?? $resultatsEvaluation['message_resultat'],
                auth()->user()->personne->id
            );

            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationEvaluation($projet, $resultatsEvaluation);

            return response()->json([
                'success' => true,
                'message' => $this->getMessageSuccesEvaluation($resultatsEvaluation['resultat_global']),
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'projet_id' => $projet->id,
                    'resultat_global' => $resultatsEvaluation['resultat_global'],
                    'nouveau_statut' => $nouveauStatut->value,
                    'evaluateur_id' => auth()->id(),
                    'date_evaluation' => now()->format('d/m/Y H:i:s'),
                    'statistiques' => $resultatsEvaluation
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les détails d'évaluation d'un TDR de faisabilité
     */
    public function getEvaluationTdr(int $projetId): JsonResponse
    {
        try {

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            if (auth()->user()->profilable->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer le TDR soumis
            $tdr = $this->tdrRepository->getModel()
                ->where('projet_id', $projetId)
                ->where('type', 'faisabilite')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$tdr) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Aucun TDR de faisabilité trouvé pour ce projet.'
                ], 404);
            }

            // Récupérer l'évaluation en cours ou la dernière évaluation via le TDR
            $evaluation = $tdr->evaluations()
                ->where('type_evaluation', 'tdr-faisabilite')
                ->with(['champs_evalue' => function ($query) {
                    $query->orderBy('ordre_affichage');
                }])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucune évaluation trouvée pour cette tdr.'
                ], 206);
            }

            // Construire la grille d'évaluation avec les données existantes
            $grilleEvaluation = [];
            if ($evaluation && $evaluation->statut == 1) {

                // Recalculer le résultat pour l'évaluation terminée
                $champs_evalues = is_string($evaluation->evaluation) ? json_decode($evaluation->evaluation)->champs_evalues : $evaluation->evaluation;

                foreach ($champs_evalues as $champ) {
                    $champ =  (array)$champ;
                    $grilleEvaluation[] = [
                        'champ_id' => isset($champ["champ_id"]) ? $champ["champ_id"] : null,
                        'label' => isset($champ["label"]) ? $champ["label"] : null,
                        'attribut' => isset($champ["attribut"]) ? $champ["attribut"] : null,
                        'type_champ' => isset($champ["type_champ"]) ? $champ["type_champ"] : "textearea",
                        'ordre_affichage' => isset($champ["ordre_affichage"]) ? $champ["ordre_affichage"] : 0,
                        'appreciation' =>  isset($champ["appreciation"]) ? $champ["appreciation"] : null,
                        'commentaire_evaluateur' =>  isset($champ["commentaire_evaluateur"]) ? $champ["commentaire_evaluateur"] : null,
                        'date_appreciation' =>  isset($champ["date_appreciation"]) ? $champ["date_appreciation"] : null,
                    ];
                }
            } else {

                // Récupérer le canevas d'appréciation des TDRs
                $canevasAppreciation = $this->documentRepository->getModel()
                    ->where('type', 'checklist')
                    ->where('slug', 'canevas-appreciation-tdrs-faisabilite')
                    ->with(['champs' => function ($query) {
                        $query->orderBy('ordre_affichage');
                    }])
                    ->first();

                $canevasAppreciation = ($this->documentRepository->getCanevasAppreciationTdrFaisabilite());

                if (!$canevasAppreciation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Canevas d\'appréciation des TDRs introuvable.'
                    ], 404);
                }

                foreach ($canevasAppreciation->all_champs as $champ) {
                    $evaluationExistante = null;
                    if ($evaluation) {
                        $evaluationExistante = $evaluation->champs_evalue->firstWhere('id', $champ->id);
                    }

                    $grilleEvaluation[] = [
                        'champ_id' => $champ->id,
                        'label' => $champ->label,
                        'attribut' => $champ->attribut,
                        'type_champ' => $champ->type_champ,
                        'ordre_affichage' => $champ->ordre_affichage,
                        'appreciation' => $evaluationExistante ? $evaluationExistante->pivot->note : null,
                        'commentaire_evaluateur' => $evaluationExistante ? $evaluationExistante->pivot->commentaires : null,
                        'date_appreciation' => $evaluationExistante ? $evaluationExistante->pivot->date_note : null
                    ];
                }
            }

            // Calculer le résultat de l'évaluation si elle existe et est terminée
            $resultatsEvaluation = null;
            $actionsSuivantes = null;
            $evaluationsChamps = [];

            if ($evaluation && $evaluation->statut == 1) {
                // Recalculer le résultat pour l'évaluation terminée
                $champs_evalues = is_string($evaluation->evaluation) ? json_decode($evaluation->evaluation)->champs_evalues : $evaluation->evaluation;
                foreach ($champs_evalues as $champ) {
                    $champ =  (array)$champ;
                    $evaluationsChamps[] = [
                        'champ_id' => isset($champ["champ_id"]) ? $champ["champ_id"] : null,
                        'label' => isset($champ["label"]) ? $champ["label"] : null,
                        'attribut' => isset($champ["attribut"]) ? $champ["attribut"] : null,
                        'type_champ' => isset($champ["type_champ"]) ? $champ["type_champ"] : "textearea",
                        'ordre_affichage' => isset($champ["ordre_affichage"]) ? $champ["ordre_affichage"] : 0,
                        'appreciation' =>  isset($champ["appreciation"]) ? $champ["appreciation"] : null,
                        'commentaire_evaluateur' =>  isset($champ["commentaire_evaluateur"]) ? $champ["commentaire_evaluateur"] : null,
                        'date_appreciation' =>  isset($champ["date_appreciation"]) ? $champ["date_appreciation"] : null,
                    ];
                }
                $resultatsEvaluation = $evaluation->resultats_evaluation;
            } else {
                foreach ($evaluation->champs_evalue as $champ) {
                    $evaluationsChamps[] = [
                        'champ_id' => $champ->id,
                        'appreciation' => $champ->pivot->note,
                        'commentaire_evaluateur' => $champ->pivot->commentaires,
                        'date_appreciation' => $champ->pivot->date_note
                    ];
                }

                $resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, ['evaluations_champs' => $evaluationsChamps]);
            }

            // Déterminer les actions suivantes selon le résultat
            $actionsSuivantes = $this->getActionsSuivantesSelonResultat($resultatsEvaluation['resultat_global']);

            // Récupérer toutes les évaluations du projet pour ce type
            $evaluations = $projet->evaluations()
                ->where('statut', 1)
                ->where('id', "<>", $evaluation->id)
                ->where('type_evaluation', 'tdr-faisabilite')
                ->with(['champs_evalue' => function ($query) {
                    $query->orderBy('ordre_affichage');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            // Construire l'historique des évaluations
            $historiqueEvaluations = $evaluations->map(function ($evaluation) {
                // Recalculer le résultat pour chaque évaluation

                //$resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, ['evaluations_champs' => $evaluationsChamps]);
                $resultatsEvaluation = $evaluation->resultats_evaluation;
                $champs_evalues = is_string($evaluation->evaluation) ? json_decode($evaluation->evaluation)->champs_evalues : $evaluation->evaluation;
                return [
                    'id' => $evaluation->id,
                    'statut' => $evaluation->statut, // 0=en cours, 1=terminée
                    'evaluateur' => $evaluation->evaluateur ? new UserResource($evaluation->evaluateur) : 'N/A',
                    'date_debut' => Carbon::parse($evaluation->date_debut_evaluation)->format("Y-m-d h:i:s"),
                    'date_fin' => Carbon::parse($evaluation->date_fin_evaluation)->format("Y-m-d h:i:s"),
                    'commentaire_global' => $evaluation->commentaire,
                    'resultat_global' => $resultatsEvaluation['resultat_global'] ?? null,
                    'message_resultat' => $resultatsEvaluation['message_resultat'] ?? null,
                    'champs_evalues' => collect($champs_evalues)->map(function ($champ) {
                        $champ = (array)$champ;
                        return [
                            'champ_id' => isset($champ["champ_id"]) ? $champ["champ_id"] : null,
                            'label' => isset($champ["label"]) ? $champ["label"] : null,
                            'attribut' => isset($champ["attribut"]) ? $champ["attribut"] : null,
                            'type_champ' =>  isset($champ["type_champ"]) ? $champ["type_champ"] : "textearea",
                            'ordre_affichage' => isset($champ["ordre_affichage"]) ? $champ["ordre_affichage"] : 0,
                            'appreciation' =>  isset($champ["appreciation"]) ? $champ["appreciation"] : null,
                            'commentaire_evaluateur' =>  isset($champ["commentaire_evaluateur"]) ? $champ["commentaire_evaluateur"] : null,
                            'date_appreciation' =>  isset($champ["date_appreciation"]) ? $champ["date_appreciation"] : null,
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Détails de l\'évaluation TDR récupérés avec succès.',
                'data' => [
                    'tdr' => new TdrResource($tdr->load(['fichiers', 'projet'])),
                    'evaluation_existante' => $evaluation ? [
                        'id' => $evaluation->id,
                        'statut' => $evaluation->statut, // 0=en cours, 1=terminée
                        'evaluateur' => new UserResource($evaluation->evaluateur),
                        'date_debut' => Carbon::parse($evaluation->date_debut_evaluation)->format("Y-m-d h:i:s"),
                        'date_fin' => Carbon::parse($evaluation->date_fin_evaluation)->format("Y-m-d h:i:s"),
                        'commentaire_global' => $evaluation->commentaire,
                        'grille_evaluation' => $grilleEvaluation,
                        'evaluation' => json_decode($evaluation->evaluation), // 0=en cours, 1=terminée
                    ] : null,
                    'canevasAppreciation' => $canevasAppreciation,
                    'resultats_evaluation' => $resultatsEvaluation,
                    'actions_suivantes' => $actionsSuivantes,
                    'historique_evaluations' => $historiqueEvaluations,
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Valider les TDRs de faisabilité (décision finale pour cas "non accepté" uniquement)
     */
    public function validerTdrs(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DGPD uniquement)
            if (!auth()->user()->hasPermissionTo('apprecier-un-tdr-de-faisabilite') && auth()->user()->type !== 'dgpd' && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n\'avez pas les droits pour effectuer cette évaluation.", 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if (!in_array($projet->statut->value, [StatutIdee::EVALUATION_TDR_F->value, StatutIdee::R_TDR_FAISABILITE->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape d\'évaluation des TDRs.'
                ], 422);
            }

            $tdr = $projet->tdrFaisabilite->first();

            if (auth()->user()->id !== $tdr->soumisPar?->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier que le TDR est soumis et peut être évalué
            if (!$tdr?->peutEtreValide()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le TDR doit être évalué avant de pouvoir être validé.'
                ], 422);
            }

            // Vérifier qu'il y a une évaluation terminée avec résultat "non accepté"
            $evaluation = $tdr?->evaluations()
                ->where('type_evaluation', 'tdr-faisabilite')
                ->where('statut', 1) // Évaluation terminée
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune évaluation terminée trouvée pour ce projet.'
                ], 422);
            }

            // Recalculer le résultat pour s'assurer qu'il est "non accepté"
            $evaluationsChamps = [];
            foreach ($evaluation->champs_evalue as $champ) {
                $evaluationsChamps[] = [
                    'champ_id' => $champ->id,
                    'appreciation' => $champ->pivot->note,
                    'commentaire' => $champ->pivot->commentaires
                ];
            }

            $resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, ['evaluations_champs' => $evaluationsChamps]);

            if ($resultatsEvaluation['resultat_global'] !== 'non-accepte') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette méthode n\'est utilisable que pour les cas "non accepté". Le résultat actuel est: ' . $resultatsEvaluation['resultat_global']
                ], 422);
            }

            // Valider l'action demandée pour les cas "non accepté"
            if (!isset($data['action']) || !in_array($data['action'], ['reviser', 'abandonner'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action invalide pour cas "non accepté". Actions possibles: reviser, abandonner.'
                ], 422);
            }

            $nouveauStatut = null;
            $messageAction = '';

            switch ($data['action']) {
                case 'reviser':
                    // Reviser malgré l'évaluation négative → retour au statut TDR_FAISABILITE
                    $nouveauStatut = StatutIdee::TDR_FAISABILITE;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut)
                    ]);

                    $tdr->update([
                        'statut' => 'retour_travail_supplementaire'
                    ]);

                    $messageAction = 'Projet continue malgré l\'évaluation négative. Retour à la soumission des TDRs.';
                    break;

                case 'abandonner':
                    // Abandonner le projet suite à l'évaluation négative
                    $nouveauStatut = StatutIdee::ABANDON;
                    $projet->update([
                        'date_fin_etude' => now(),
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut)
                    ]);
                    $messageAction = 'Projet abandonné suite à l\'évaluation négative des TDRs.';
                    break;
            }

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, $nouveauStatut);
            $this->enregistrerDecision(
                $projet,
                "Décision finale TDRs faisabilité - " . ucfirst($data['action']),
                $data['commentaire'] ?? $messageAction,
                auth()->user()->personne->id
            );

            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationValidation($projet, $data['action'], $data);

            return response()->json([
                'success' => true,
                'message' => $messageAction,
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'projet_id' => $projet->id,
                    'action' => $data['action'],
                    'ancien_statut' => StatutIdee::EVALUATION_TDR_F->value,
                    'nouveau_statut' => $nouveauStatut->value,
                    'commentaire' => $data['commentaire'] ?? null,
                    'decision_par' => auth()->id(),
                    'decision_le' => now()->format('d/m/Y H:i:s'),
                    'resultats_evaluation' => $resultatsEvaluation
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidationEtude(int $projetId): JsonResponse
    {
        try {

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            if (auth()->user()->profilable->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier que le projet est à l'étape d'évaluation ou post-évaluation
            if (!in_array($projet->statut->value, [
                StatutIdee::EVALUATION_TDR_F->value,
                StatutIdee::SOUMISSION_RAPPORT_F->value,
                StatutIdee::VALIDATION_F->value,
                StatutIdee::R_TDR_FAISABILITE->value,
                StatutIdee::TDR_FAISABILITE->value,

                StatutIdee::PRET->value,
                StatutIdee::MATURITE->value,
                StatutIdee::RAPPORT->value,
                StatutIdee::ABANDON->value
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à une étape permettant la consultation des détails de validation.'
                ], 422);
            }

            // Récupérer l'évaluation en cours ou la dernière évaluation selon le statut
            $evaluationValidation = null;

            // Pour le statut VALIDATION_PF, récupérer l'évaluation de validation
            $evaluationValidation = $projet->evaluations()
                ->where('type_evaluation', 'validation-etude-faisabilite')
                ->with(['champs_evalue' => function ($query) {
                    $query->orderBy('ordre_affichage');
                }])
                ->orderBy('created_at', 'desc')
                ->first();

            // Récupérer les fichiers de validation attachés au projet
            $fichiersValidation = $projet->fichiers()
                ->where('categorie', 'rapport-validation-faisabilite')
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Détails de validation etude de faisabilité récupérés avec succès.',
                'data' => [
                    'projet' => new ProjetResource($projet),
                    'tdr' => new TdrResource($projet->tdrFaisabilite->first()),
                    'rapport' => new RapportResource($projet->rapportFaisabilite()->first()),

                    'evaluation_validation' => $evaluationValidation ? [
                        'id' => $evaluationValidation->id,
                        'evaluation' => $evaluationValidation->evaluation,
                        'decision' => $evaluationValidation->resultats_evaluation,
                        'statut' => $evaluationValidation->statut, // 0=en cours, 1=terminée
                        'evaluateur' => new UserResource($evaluationValidation->evaluateur),
                        'date_debut' => Carbon::parse($evaluationValidation->date_debut_evaluation)->format("Y-m-d h:i:s"),
                        'date_fin' => Carbon::parse($evaluationValidation->date_fin_evaluation)->format("Y-m-d h:i:s"),
                        'commentaire_global' => $evaluationValidation->commentaire
                    ] : null,
                    'fichiers_validation' => FichierResource::collection($fichiersValidation),
                    'checklist_suivi_validation' => ($evaluationValidation && $evaluationValidation->evaluation && isset($evaluationValidation->evaluation["champs_evalues"])) ? $evaluationValidation->evaluation["champs_evalues"] : null
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
    /**
     * Soumettre le rapport de faisabilité (SFD-016)
     */
    public function soumettreRapportFaisabilite(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            if (!auth()->user()->hasPermissionTo('soumettre-un-rapport-de-faisabilite') && auth()->user()->type !== 'dpaf' && auth()->user()->profilable_type !== Dpaf::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::SOUMISSION_RAPPORT_F->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de soumission du rapport de faisabilité.'
                ], 422);
            }

            // Déterminer si c'est une soumission ou un brouillon
            $action = $data['action'] ?? 'submit';
            $estBrouillon = $action === 'draft';

            if (!$estBrouillon) {
                // Validation des checklists pour la soumission finale
                $checklists = [
                    'checklist_suivi_assurance_qualite' => 'Veuillez remplir la checklist de suivi assurance qualité !',
                    'checklist_etude_faisabilite_technique' => 'Veuillez remplir la checklist d\'étude de faisabilité technique !',
                    'checklist_etude_faisabilite_economique' => 'Veuillez remplir la checklist d\'étude de faisabilité économique !',
                    'checklist_etude_faisabilite_marche' => 'Veuillez remplir la checklist d\'étude de faisabilité marché !',
                    'checklist_etude_faisabilite_organisationnelle_juridique' => 'Veuillez remplir la checklist d\'étude de faisabilité organisationnelle et juridique !',
                    'checklist_suivi_analyse_faisabilite_financiere' => 'Veuillez remplir la checklist de suivi analyse de faisabilité financière !',
                    'checklist_suivi_etude_analyse_impact_environnemental_social' => 'Veuillez remplir la checklist de suivi étude analyse d\'impact environnemental et social !'
                ];

                foreach ($checklists as $checklistKey => $message) {
                    if (empty(data_get($data, $checklistKey))) {
                        throw ValidationException::withMessages([
                            $checklistKey => $message
                        ]);
                    }
                }

                if (!isset($data['etude_faisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "est_finance" => "Veuillez préciser si l'etude de faisabilité est financé ou pas !"
                    ]);
                }
            }

            // Récupérer le dernier rapport de faisabilité s'il existe
            $rapportExistant = $projet->rapportFaisabilite()->first();

            // Préparer les données du rapport - collecter toutes les checklists
            $checklistData = [
                'checklist_suivi_assurance_qualite' => $data['checklist_suivi_assurance_qualite'] ?? null,
                'checklist_etude_faisabilite_technique' => $data['checklist_etude_faisabilite_technique'] ?? null,
                'checklist_etude_faisabilite_economique' => $data['checklist_etude_faisabilite_economique'] ?? null,
                'checklist_etude_faisabilite_marche' => $data['checklist_etude_faisabilite_marche'] ?? null,
                'checklist_etude_faisabilite_organisationnelle_juridique' => $data['checklist_etude_faisabilite_organisationnelle_juridique'] ?? null,
                'checklist_suivi_analyse_faisabilite_financiere' => $data['checklist_suivi_analyse_faisabilite_financiere'] ?? null,
                'checklist_suivi_etude_analyse_impact_environnemental_social' => $data['checklist_suivi_etude_analyse_impact_environnemental_social'] ?? null,
            ];

            $rapportData = [
                'projet_id' => $projet->id,
                'type' => 'faisabilite',
                'statut' => $estBrouillon ? 'brouillon' : 'soumis',
                'intitule' => 'Rapport de faisabilité',
                'checklist_suivi' => $checklistData,
                'info_cabinet_etude' => [
                    'nom_cabinet' => $data['cabinet_etude']['nom_cabinet'] ?? null,
                    'contact_cabinet' => $data['cabinet_etude']['contact_cabinet'] ?? null,
                    'email_cabinet' => $data['cabinet_etude']['email_cabinet'] ?? null,
                    'adresse_cabinet' => $data['cabinet_etude']['adresse_cabinet'] ?? null,
                ],
                'recommandation' => $data['recommandation'] ?? null,
                'soumis_par_id' => auth()->id()
            ];

            // Définir la date de soumission seulement si c'est une soumission finale
            if (!$estBrouillon) {
                $rapportData['date_soumission'] = now();
            }

            // Créer ou mettre à jour le rapport
            if ($rapportExistant && $rapportExistant->statut === 'brouillon') {
                // Mettre à jour le rapport existant s'il est en brouillon
                $rapport = $rapportExistant;
                $rapport->fill($rapportData);
                $rapport->save();
                $message = $estBrouillon ? 'Rapport sauvegardé en brouillon.' : 'Rapport soumis avec succès.';
            } elseif ($rapportExistant && $rapportExistant->statut === 'soumis' && !$estBrouillon) {
                // Si un rapport soumis existe déjà et qu'on soumet à nouveau, créer une nouvelle version
                $rapportData['parent_id'] = $rapportExistant->id;
                $rapport = Rapport::create($rapportData);
                $message = 'Nouvelle version du rapport soumise avec succès.';
            } else {
                // Créer un nouveau rapport (première version)
                $rapport = Rapport::create($rapportData);
                $message = $estBrouillon ? 'Rapport sauvegardé en brouillon.' : 'Rapport soumis avec succès.';
            }

            $this->traiterChampsChecklistsSuiviFaisabilite($rapport, $checklistData);

            // Changer le statut du projet seulement pour les soumissions finales
            if (!$estBrouillon) {

                // Traitement et sauvegarde du fichier rapport de faisabilité
                $fichierRapport = null;
                if (isset($data['rapport'])) {
                    $fichierRapport = $this->gererFichierRapportFaisabilite($rapport, $data['rapport'], $data);
                }

                // Traitement et sauvegarde du rapport proces verbal
                $fichierCoutsAvantages = null;
                if (isset($data['proces_verbal'])) {
                    $this->gererFichierProcesVerbal($rapport, $data['proces_verbal'], $data);
                }

                if (isset($data['liste_presence'])) {
                    $this->gererFichierListePresence($rapport, $data['liste_presence'], $data);
                }

                $info_etude_faisabilite = $projet->info_etude_faisabilite ?? [];

                //validation des informations de si l'étude de faisabilité est financée
                if (!isset($data['etude_faisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "etude_faisabilite.est_finance" => "Le champ 'est_finance' est obligatoire."
                    ]);
                }

                // on doit valider si c'est une valeur booléenne
                // par exemple une chaîne de caractères, un entier, un tableau, etc.
                // mais si la valeur est 0 ou 1, on peut la considérer comme booléenne

                if (is_string($data['etude_faisabilite']['est_finance'])) {
                    $valeur = strtolower($data['etude_faisabilite']['est_finance']);
                    if ($valeur === 'true' || $valeur === '1') {
                        $data['etude_faisabilite']['est_finance'] = true;
                    } elseif ($valeur === 'false' || $valeur === '0') {
                        $data['etude_faisabilite']['est_finance'] = false;
                    } else {
                        throw ValidationException::withMessages([
                            "etude_faisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                        ]);
                    }
                } elseif (is_int($data['etude_faisabilite']['est_finance'])) {
                    if ($data['etude_faisabilite']['est_finance'] === 1) {
                        $data['etude_faisabilite']['est_finance'] = true;
                    } elseif ($data['etude_faisabilite']['est_finance'] === 0) {
                        $data['etude_faisabilite']['est_finance'] = false;
                    } else {
                        throw ValidationException::withMessages([
                            "etude_faisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                        ]);
                    }
                } elseif (is_array($data['etude_faisabilite']['est_finance']) || is_null($data['etude_faisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "etude_faisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                    ]);
                } else {
                    // Si c'est déjà une valeur booléenne, ne rien faire
                }

                if (!is_bool($data['etude_faisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "etude_faisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                    ]);
                }

                $est_finance = $data['etude_faisabilite']['est_finance'] ?? ($info_etude_faisabilite['est_finance'] ?? false);

                // Mettre à jour les informations de l'étude de faisabilité dans le projet
                $projet->update([
                    // Fusionner avec les nouvelles valeurs provenant de $data
                    'info_etude_faisabilite' => array_merge($info_etude_faisabilite, [
                        'date_demande'   => $data['etude_faisabilite']['date_demande'] ?? ($info_etude_faisabilite['date_demande'] ?? null),
                        'date_obtention' => $data['etude_faisabilite']['date_obtention'] ?? ($info_etude_faisabilite['date_obtention'] ?? null),
                        'montant'        => $data['etude_faisabilite']['montant'] ?? ($info_etude_faisabilite['montant'] ?? null),
                        'reference'      => $data['etude_faisabilite']['reference'] ?? ($info_etude_faisabilite['reference'] ?? null),
                        'est_finance'    => $est_finance,
                    ]),

                    'statut' => StatutIdee::VALIDATION_F,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_F),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_F)
                ]);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($projet, StatutIdee::VALIDATION_F);
                $this->enregistrerDecision(
                    $projet,
                    "Soumission du rapport de faisabilité",
                    "Rapport ID: {$rapport->id} soumis par cabinet: " . ($rapport->info_cabinet_etude['nom_cabinet'] ?? 'N/A'),
                    auth()->user()->personne->id
                );

                // Envoyer une notification
                //$this->envoyerNotificationSoumissionRapport($projet, $rapport, $fichierRapport);
            }

            DB::commit();

            // Charger les relations nécessaires pour le resource
            $rapport->load(['fichiers', 'soumisPar', 'projet']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'rapport_id' => $rapport->id,
                    'projet_id' => $projet->id,
                    'statut_rapport' => $rapport->statut,
                    'statut_projet' => $projet->statut->value,
                    'action' => $estBrouillon ? 'draft' : 'submit',
                    'rapport' => new RapportResource($rapport)
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les détails de soumission du rapport de faisabilité
     */
    public function getDetailsSoumissionRapportFaisabilite(int $projetId): JsonResponse
    {
        try {
            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier les permissions d'accès
            /* if (auth()->user()->profilable?->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== \App\Models\Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'accès pour effectuer cette action", 403);
            } */

            // Récupérer le rapport soumis le plus récent
            $rapport = \App\Models\Rapport::where('projet_id', $projetId)
                ->where('type', 'faisabilite')
                ->where('statut', 'soumis')
                ->with(['fichiersRapport', 'procesVerbaux', 'soumisPar', 'projet', 'champs', 'documentsAnnexes'])
                ->latest('created_at')
                ->first();

            if (!$rapport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun rapport soumis trouvé pour ce projet.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new \App\Http\Resources\RapportResource($rapport),
                'message' => 'Détails de soumission du rapport de faisabilité récupérés avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rapport soumis: ' . $e->getMessage(),
                'data' => null
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }

    /**
     * Valider l'étude de faisabilité (SFD-017)
     */
    public function validerEtudeFaisabilite(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (Comité de validation Ministériel uniquement)
            if (!in_array(auth()->user()->type, ['comite_validation', 'admin']) && !auth()->user()->hasPermissionTo('valider-etude-faisabilite')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette validation.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            /* if ($projet->statut->value !== StatutIdee::VALIDATION_F->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de validation de faisabilité.'
                ], 422);
            } */

            if ($data['action'] != 'sauvegarder') {

                if (empty(data_get($data, 'checklist_suivi_validation'))) {
                    throw ValidationException::withMessages([
                        "checklist_suivi_validation" => "Veuillez faire le suivi du rapport de faisabilité pour la validation !"
                    ]);
                }

                // Valider les informations de financement si le projet est marqué comme financé
                if (
                    isset($projet->info_etude_faisabilite['est_finance']) &&
                    !empty($projet->info_etude_faisabilite['est_finance'])
                ) {
                    $est_finance = $projet->info_etude_faisabilite['est_finance'];
                    // Convertir en booléen si nécessaire
                    if (is_string($est_finance)) {
                        $est_finance = strtolower($est_finance) === 'true' || $est_finance === '1';
                    } else {
                        $est_finance = filter_var($est_finance, FILTER_VALIDATE_BOOLEAN);
                    }

                    if($est_finance) {

                        if (isset($data['etude_faisabilite'])) {
                            // si c'est une string JSON → on la décode
                            if (is_string($data['etude_faisabilite'])) {
                                throw new Exception("Error Processing Request", 1);

                                $decoded = json_decode($data['etude_faisabilite'], true);

                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $data['etude_faisabilite'] = $decoded;
                                } else {
                                    throw ValidationException::withMessages([
                                        "etude_faisabilite" => "Format JSON invalide pour les informations de financement."
                                    ]);
                                }
                            }
                            // si c'est déjà un tableau → on ne fait rien
                            elseif (!is_array($data['etude_faisabilite'])) {
                                throw ValidationException::withMessages([
                                    "etude_faisabilite" => "Les informations de financement doivent être un tableau valide."
                                ]);
                            }
                        } else {
                            throw ValidationException::withMessages([
                                "etude_faisabilite" => "Les informations de financement sont requises lorsque le projet est financé."
                            ]);
                        }

                        $requiredFields = ['date_demande', 'date_obtention', 'montant', 'reference'];

                         foreach ($requiredFields as $field) {
                            // validation de présence de $data['etude_faisabilite'][$field]
                            if (!isset($data['etude_faisabilite'][$field]) && !empty($data['etude_faisabilite'][$field])) {
                                throw ValidationException::withMessages([
                                    "etude_faisabilite.$field" => "Le champ $field est obligatoire lorsque le projet est financé. " . $data['etude_faisabilite'][$field]
                                ]);
                            }
                            // validations supplémentaires pour les champs spécifiques
                            // Il faut savoir que les donnees sont soumis dans un formdata donc tout est string

                            if ($field === 'montant' && (!is_numeric($data['etude_faisabilite'][$field]) || $data['etude_faisabilite'][$field] <= 0)) {
                                throw ValidationException::withMessages([
                                    "etude_faisabilite.$field" => "Le montant doit être un nombre positif."
                                ]);
                            }

                            // Ajouter d'autres validations spécifiques si nécessaire
                            if (in_array($field, ['date_demande', 'date_obtention'])) {
                                $date = \DateTime::createFromFormat('Y-m-d', $data['etude_faisabilite'][$field]);
                                if (!$date || $date->format('Y-m-d') !== $data['etude_faisabilite'][$field]) {
                                    throw ValidationException::withMessages([
                                        "etude_faisabilite.$field" => "Le champ $field doit être une date valide au format AAAA-MM-JJ."
                                    ]);
                                }
                            }

                            if ($field === 'reference' && strlen($data['etude_faisabilite'][$field]) > 100) {
                                throw ValidationException::withMessages([
                                    "etude_faisabilite.$field" => "La référence ne doit pas dépasser 100 caractères."
                                ]);
                            }
                        }

                        // Toutes les validations sont passées, on peut enregistrer les informations
                        // enregistrer les informations de financement dans le projet info etude de faisabilité
                        // merge avec les données existantes pour ne pas écraser d'autres infos
                        $projet->info_etude_faisabilite = array_merge($projet->info_etude_faisabilite ?? [], [
                            'est_finance' => $est_finance,
                            // recuperer les autres champs depuis $data

                            'date_demande' => $data['etude_faisabilite']['date_demande'],
                            'date_obtention' => $data['etude_faisabilite']['date_obtention'],
                            'montant' => $data['etude_faisabilite']['montant'],
                            'reference' => $data['etude_faisabilite']['reference'],
                        ]);


                        $projet->save();
                    }
                }
            }


            /**
             * Valider l'étude de faisabilité selon l'action demandée
             * Actions possibles:
             * - maturite : Projet à maturité → passe au statut MATURITE
             * - reprendre : Reprendre l'étude de faisabilité → retourne au statut SOUMISSION_RAPPORT_F
             * - abandonner : Abandonner le projet → passe au statut ABANDON
             * - sauvegarder : Sauvegarder les données sans changer le statut
             *
             * Chaque action doit être justifiée par un commentaire.
             * Si l'action est "maturite", le type de projet doit être mis à jour en conséquence.
             * Si l'action est "reprendre" ou "abandonner", le type de projet reste inchangé.
             * Si l'action est "sauvegarder", aucune modification de statut ou type de projet n'est effectuée.
             *
             */

            // Valider l'action demandée
            $actionsPermises = ['maturite', 'reprendre', 'abandonner', 'sauvegarder'];

            if (!isset($data['action']) || !in_array($data['action'], $actionsPermises)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action invalide. Actions possibles: ' . implode(', ', $actionsPermises)
                ], 422);
            }

            $nouveauStatut = null;
            $messageAction = '';
            $typeProjet = null;

            // Créer une évaluation pour tracer la validation
            $evaluationValidation = $projet->evaluations()->updateOrCreate([
                'type_evaluation' => 'validation-etude-faisabilite',
                'projetable_type' => get_class($projet),
                'projetable_id' => $projet->id
            ],[
                'date_debut_evaluation' => now(),
                'date_fin_evaluation' => now(),
                'valider_le' => now(),
                'evaluateur_id' => auth()->id(),
                'valider_par' => auth()->id(),
                'commentaire' => $data['commentaire'] ?? $messageAction,
                'evaluation' => $data,
                'resultats_evaluation' => $data['action'],
                'statut' => $data['action'] != 'sauvegarder' ? 1 : 0
            ]);

            // Vérifier la cohérence du suivi rapport si des données de validation sont fournies
            if (isset($data['checklist_suivi_validation'])) {

                // Enregistrer les appréciations pour chaque champ

                $syncData = [];

                foreach ($data['checklist_suivi_validation'] as $evaluationChamp) {
                    $syncData[$evaluationChamp['checkpoint_id']] = [
                        'note' => $evaluationChamp['remarque'],
                        'date_note' => now(),
                        'commentaires' => $evaluationChamp['explication'] ?? null,
                    ];
                }

                $evaluationValidation->champs_evalue()->sync($syncData);

                // Préparer l'évaluation complète pour enregistrement
                $evaluationComplete = [
                    'champs_evalues' => collect($this->documentRepository->getCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite()->all_champs)->map(function ($champ) use ($evaluationValidation) {
                        $champEvalue = collect($evaluationValidation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                        return [
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
                    'decision' => ['decision' => $data['action'], 'commentaire' => $data['commentaire']],
                ];

                // Mettre à jour l'évaluation avec les données complètes
                $evaluationValidation->fill([
                    'evaluation' => $evaluationComplete,
                ]);

                $evaluationValidation->save();

                $resultVerificationCoherence = $this->verifierCoherenceSuiviRapport($projet, $data['checklist_suivi_validation']);
                if (!$resultVerificationCoherence['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $resultVerificationCoherence['message'],
                        'incoherences' => $resultVerificationCoherence['incoherences'] ?? []
                    ], 422);
                }

                // Vérifier que tous les checkpoints obligatoires sont présents et complétés
                $resultVerificationCompletude = $this->verifierCompletude($data['checklist_suivi_validation']);
                if (!$resultVerificationCompletude['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $resultVerificationCompletude['message'],
                        'checkpoints_incomplets' => $resultVerificationCompletude['checkpoints_incomplets'] ?? []
                    ], 422);
                }
            }

            switch ($data['action']) {
                case 'maturite':
                    // Projet à maturité
                    $nouveauStatut = StatutIdee::MATURITE;
                    $typeProjet = TypesProjet::complex2;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut),
                        'type_projet' => $typeProjet,
                        'date_fin_etude' => now()
                    ]);
                    $messageAction = 'Projet validé comme étant à maturité.';
                    break;

                case 'reprendre':
                    // Reprendre l'étude de faisabilité
                    $nouveauStatut = StatutIdee::SOUMISSION_RAPPORT_F;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut)
                    ]);
                    $messageAction = 'Projet renvoyé pour reprendre l\'étude de faisabilité.';
                    break;

                case 'abandonner':
                    // Abandonner le projet
                    $nouveauStatut = StatutIdee::ABANDON;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut),
                        'date_fin_etude' => now()
                    ]);
                    $messageAction = 'Projet abandonné lors de la validation.';
                    break;

                case 'sauvegarder':
                    // Sauvegarder sans changer le statut
                    $this->sauvegarderDonneesValidation($projet, $data);
                    $messageAction = 'Données de validation sauvegardées sans changement de statut.';
                    // Pas de changement de statut
                    break;
                default:
                    // Récupérer l'ancien contenu JSON ou un tableau vide
                    $info = $projet->info_etude_faisabilite ?? [];

                    // Fusionner avec les nouvelles valeurs provenant de $data
                    $info = array_merge($info, [
                        'date_demande'   => $data['etude_faisabilite']['date_demande'] ?? null,
                        'date_obtention' => $data['etude_faisabilite']['date_obtention'] ?? null,
                        'montant'        => $data['etude_faisabilite']['montant'] ?? null,
                        'reference'      => $data['etude_faisabilite']['reference'] ?? null,
                    ]);

                    // Mettre à jour le modèle
                    $projet->update([
                        'info_etude_faisabilite' => $info,
                    ]);
            }

            // Attacher le fichier rapport de validation si fourni
            if (isset($data['rapport_validation_etude']) && $data['action'] !== 'sauvegarder') {
                $this->attacherFichierRapportValidation($projet, $data['rapport_validation_etude'], $evaluationValidation);
            }

            // Enregistrer le workflow et la décision si le statut a changé
            if ($nouveauStatut) {
                $this->enregistrerWorkflow($projet, $nouveauStatut);
            }

            $this->enregistrerDecision(
                $projet,
                "Validation faisabilité - " . ucfirst($data['action']),
                $data['commentaire'] ?? $messageAction,
                auth()->user()->personne->id
            );

            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationValidationFaisabilite($projet, $data['action'], $data);

            return response()->json([
                'success' => true,
                'message' => $messageAction,
                'data' => [
                    'projet_id' => $projet->id,
                    'action' => $data['action'],
                    'ancien_statut' => StatutIdee::VALIDATION_F->value,
                    'nouveau_statut' => $nouveauStatut ? $nouveauStatut->value : StatutIdee::VALIDATION_F->value,
                    'type_projet' => $typeProjet ? $typeProjet->value : null,
                    'commentaire' => $data['commentaire'] ?? null,
                    'valide_par' => auth()->id(),
                    'valide_le' => now()->format('d/m/Y H:i:s'),
                    'date_fin_etude' => in_array($data['action'], ['maturite', 'abandonner']) ? now()->format('d/m/Y H:i:s') : null
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }
    /**
     * Récupérer le rapport soumis pour un projet
     */
    public function getRapportSoumis(int $projetId): JsonResponse
    {
        try {
            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Récupérer le rapport de faisabilité soumis
            $fichierRapport = $projet->fichiers()
                ->where('categorie', 'rapport-faisabilite')
                ->where('is_active', true)
                ->with('uploadedBy.personne')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$fichierRapport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun rapport de faisabilité soumis trouvé pour ce projet.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rapport récupéré avec succès.',
                'data' => [
                    'projet' => [
                        'id' => $projet->id,
                        'intitule' => $projet->intitule,
                        'statut' => $projet->statut,
                        'phase' => $projet->phase,
                        'sous_phase' => $projet->sous_phase
                    ],
                    'rapport' => [
                        'id' => $fichierRapport->id,
                        'nom_original' => $fichierRapport->nom_original,
                        'nom_stockage' => $fichierRapport->nom_stockage,
                        'extension' => $fichierRapport->extension,
                        'taille' => $fichierRapport->taille,
                        'taille_formatee' => $fichierRapport->taille_formatee,
                        'date_soumission' => Carbon::parse($fichierRapport->created_at)->format('d/m/Y H:i:s'),
                        'soumis_par' => new UserResource($fichierRapport->uploadedBy),
                        'url_telechargement' => $fichierRapport->url,
                        'metadata' => $fichierRapport->metadata
                    ],
                    'cabinet_etude' => $fichierRapport->metadata['cabinet'] ?? null,
                    'recommandation_adaptation' => $fichierRapport->metadata['recommandation_adaptation'] ?? null
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la récupération du rapport. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }
    /**
     * Traiter toutes les checklists d'étude de faisabilité
     */
    private function traiterToutesLesChecklistsEtudeFaisabilite(Projet $projet, array $data, bool $estBrouillon): void
    {
        $checklistsEtudeFaisabilite = [
            'checklist_etude_faisabilite_marche' => 'traiterChecklistEtudeFaisabiliteMarche',
            'checklist_etude_faisabilite_economique' => 'traiterChecklistEtudeFaisabiliteEconomique',
            'checklist_etude_faisabilite_technique' => 'traiterChecklistEtudeFaisabiliteTechnique',
            'checklist_etude_faisabilite_organisationnelle_et_juridique' => 'traiterChecklistEtudeFaisabiliteOrganisationnelleEtJuridique',
            'checklist_suivi_analyse_faisabilite_financiere' => 'traiterChecklistSuiviAnalyseFaisabiliteFinanciere',
            'checklist_suivi_etude_analyse_impact_environnementale_et_sociale' => 'traiterChecklistSuiviEtudeAnalyseImpactEnvironnementaleEtSociale'
        ];

        foreach ($checklistsEtudeFaisabilite as $checklistKey => $methodName) {
            if (isset($data[$checklistKey]) && !empty($data[$checklistKey])) {
                $this->$methodName($projet, $data[$checklistKey], $estBrouillon);
            }
        }
    }

    /**
     * Traiter la checklist d'étude de faisabilité marché
     */
    private function traiterChecklistEtudeFaisabiliteMarche(Projet $projet, array $checklistData, bool $estBrouillon): void
    {
        // Récupérer les métadonnées existantes
        $metadata = $projet->metadata ?? [];

        // Ajouter ou mettre à jour les informations de checklist d'étude faisabilité marché
        $metadata['checklist_etude_faisabilite_marche'] = [
            'data' => $checklistData,
            'est_brouillon' => $estBrouillon,
            'date_traitement' => now(),
            'traite_par' => auth()->id(),
            'statut' => $estBrouillon ? 'brouillon' : 'soumis'
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);

        // Log pour traçabilité
        \Log::info('Checklist étude faisabilité marché traitée', [
            'projet_id' => $projet->id,
            'est_brouillon' => $estBrouillon,
            'traite_par' => auth()->id()
        ]);
    }

    /**
     * Traiter la checklist d'étude de faisabilité économique
     */
    private function traiterChecklistEtudeFaisabiliteEconomique(Projet $projet, array $checklistData, bool $estBrouillon): void
    {
        $this->traiterChecklistGenerique($projet, $checklistData, $estBrouillon, 'checklist_etude_faisabilite_economique', 'Checklist étude faisabilité économique traitée');
    }

    /**
     * Traiter la checklist d'étude de faisabilité technique
     */
    private function traiterChecklistEtudeFaisabiliteTechnique(Projet $projet, array $checklistData, bool $estBrouillon): void
    {
        $this->traiterChecklistGenerique($projet, $checklistData, $estBrouillon, 'checklist_etude_faisabilite_technique', 'Checklist étude faisabilité technique traitée');
    }

    /**
     * Traiter la checklist d'étude de faisabilité organisationnelle et juridique
     */
    private function traiterChecklistEtudeFaisabiliteOrganisationnelleEtJuridique(Projet $projet, array $checklistData, bool $estBrouillon): void
    {
        $this->traiterChecklistGenerique($projet, $checklistData, $estBrouillon, 'checklist_etude_faisabilite_organisationnelle_et_juridique', 'Checklist étude faisabilité organisationnelle et juridique traitée');
    }

    /**
     * Traiter la checklist de suivi analyse faisabilité financière
     */
    private function traiterChecklistSuiviAnalyseFaisabiliteFinanciere(Projet $projet, array $checklistData, bool $estBrouillon): void
    {
        $this->traiterChecklistGenerique($projet, $checklistData, $estBrouillon, 'checklist_suivi_analyse_faisabilite_financiere', 'Checklist suivi analyse faisabilité financière traitée');
    }

    /**
     * Traiter la checklist de suivi étude analyse impact environnementale et sociale
     */
    private function traiterChecklistSuiviEtudeAnalyseImpactEnvironnementaleEtSociale(Projet $projet, array $checklistData, bool $estBrouillon): void
    {
        $this->traiterChecklistGenerique($projet, $checklistData, $estBrouillon, 'checklist_suivi_etude_analyse_impact_environnementale_et_sociale', 'Checklist suivi étude analyse impact environnementale et sociale traitée');
    }

    /**
     * Méthode générique pour traiter une checklist
     */
    private function traiterChecklistGenerique(Projet $projet, array $checklistData, bool $estBrouillon, string $checklistKey, string $logMessage): void
    {
        // Récupérer les métadonnées existantes
        $metadata = $projet->metadata ?? [];

        // Ajouter ou mettre à jour les informations de la checklist
        $metadata[$checklistKey] = [
            'data' => $checklistData,
            'est_brouillon' => $estBrouillon,
            'date_traitement' => now(),
            'traite_par' => auth()->id(),
            'statut' => $estBrouillon ? 'brouillon' : 'soumis'
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);

        // Log pour traçabilité
        \Log::info($logMessage, [
            'projet_id' => $projet->id,
            'checklist_key' => $checklistKey,
            'est_brouillon' => $estBrouillon,
            'traite_par' => auth()->id()
        ]);
    }

    /**
     * Traiter la checklist de suivi assurance qualité rapport faisabilité
     */
    private function traiterChecklistSuiviAssuranceQualiteRapportFaisabilite(
        Projet $projet,
        array $checklistData,
        bool $estBrouillon,
        array $fichiersData = []
    ): array {
        try {
            // Récupérer les métadonnées existantes
            $metadata = $projet->metadata ?? [];

            // Valider les données de la checklist si nécessaire
            if (!$estBrouillon && !$this->validerChecklistSuiviAssuranceQualite($checklistData, $fichiersData)) {
                return [
                    'success' => false,
                    'message' => 'La checklist de suivi assurance qualité est incomplète ou invalide.'
                ];
            }

            // Ajouter ou mettre à jour les informations de checklist de suivi
            $metadata['checklist_suivi_assurance_qualite_faisabilite'] = [
                'data' => $checklistData,
                'fichiers_associes' => [
                    'rapport' => $fichiersData['rapport'] ? 'présent' : 'absent',
                    'proces_verbal' => $fichiersData['proces_verbal'] ? 'présent' : 'absent',
                    'cabinet_renseigne' => !empty($fichiersData['cabinet_etude']),
                    'recommandation_fournie' => !empty($fichiersData['recommandation'])
                ],
                'est_brouillon' => $estBrouillon,
                'date_traitement' => now(),
                'traite_par' => auth()->id(),
                'statut' => $estBrouillon ? 'brouillon' : 'validee'
            ];

            // Mettre à jour le projet
            $projet->update(['metadata' => $metadata]);

            // Log pour traçabilité
            \Log::info('Checklist suivi assurance qualité faisabilité traitée', [
                'projet_id' => $projet->id,
                'est_brouillon' => $estBrouillon,
                'traite_par' => auth()->id(),
                'fichiers_status' => $metadata['checklist_suivi_assurance_qualite_faisabilite']['fichiers_associes']
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            \Log::error('Erreur lors du traitement de la checklist suivi assurance qualité', [
                'projet_id' => $projet->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du traitement de la checklist de suivi assurance qualité.'
            ];
        }
    }

    /**
     * Valider la checklist de suivi assurance qualité
     */
    private function validerChecklistSuiviAssuranceQualite(array $checklistData, array $fichiersData): bool
    {
        // Vérifications de base
        if (empty($checklistData)) {
            return false;
        }

        // Vérifier la présence des fichiers essentiels pour la soumission finale
        $fichiersEssentiels = [
            'rapport' => $fichiersData['rapport'] ?? null,
            'cabinet_etude' => $fichiersData['cabinet_etude'] ?? null
        ];

        foreach ($fichiersEssentiels as $cle => $valeur) {
            if (empty($valeur)) {
                \Log::warning("Fichier essentiel manquant pour validation checklist: {$cle}");
                return false;
            }
        }

        // Ici on pourrait ajouter d'autres validations spécifiques selon les règles métier
        // Par exemple, vérifier que certains champs obligatoires de la checklist sont remplis

        return true;
    }

    // === MÉTHODES UTILITAIRES (identiques à TdrfaisabiliteService) ===

    /**
     * Obtenir les actions possibles pour la validation
     */
    private function getActionsPossiblesValidation($statut): array
    {
        return match ($statut) {
            StatutIdee::VALIDATION_F => [
                'maturite' => 'Valider le projet à maturité',
                'reprendre' => 'Reprendre l\'étude de faisabilité',
                'abandonner' => 'Abandonner le projet',
                'sauvegarder' => 'Sauvegarder sans changer le statut'
            ],
            default => []
        };
    }

    /**
     * Obtenir les actions possibles selon le statut et le résultat d'évaluation
     */
    private function getActionsPossibles($statut, $resultatEvaluation = null): array
    {
        return match ($statut) {
            StatutIdee::EVALUATION_TDR_F => [
                'evaluer' => 'Procéder à l\'évaluation des TDRs',
                // Actions de décision finale seulement pour cas "non accepté"
                ...(($resultatEvaluation === 'non-accepte') ? [
                    'reviser' => 'Reviser tdr malgré l\'évaluation négative',
                    'abandonner' => 'Abandonner le projet'
                ] : [])
            ],
            default => []
        };
    }

    /**
     * Obtenir les actions suivantes selon le résultat d'évaluation
     */
    private function getActionsSuivantesSelonResultat(string $resultatGlobal): array
    {
        return match ($resultatGlobal) {
            'passe' => [
                'type' => 'automatique',
                'message' => 'Évaluation réussie. Le projet passera automatiquement à l\'étape de soumission du rapport.',
                'action_automatique' => 'SOUMISSION_RAPPORT_F',
                'actions_manuelles' => []
            ],
            'retour' => [
                'type' => 'automatique',
                'message' => 'Des améliorations sont nécessaires. Le projet retournera automatiquement à l\'étape de soumission des TDRs.',
                'action_automatique' => 'R_TDR_FAISABILITE',
                'actions_manuelles' => []
            ],
            'non-accepte' => [
                'type' => 'decision_requise',
                'message' => 'Évaluation négative. Une décision manuelle est requise.',
                'action_automatique' => null,
                'actions_manuelles' => [
                    [
                        'action' => 'reviser',
                        'libelle' => 'Reviser tdr malgré l\'évaluation',
                        'description' => 'Permettre au projet de reviser avec de nouveaux TDRs',
                        'consequence' => 'Retour à l\'étape TDR_FAISABILITE'
                    ],
                    [
                        'action' => 'abandonner',
                        'libelle' => 'Abandonner le projet',
                        'description' => 'Mettre fin au projet suite à l\'évaluation négative',
                        'consequence' => 'Statut ABANDON'
                    ]
                ]
            ],
            default => [
                'type' => 'indefini',
                'message' => 'Résultat d\'évaluation non reconnu.',
                'action_automatique' => null,
                'actions_manuelles' => []
            ]
        };
    }

    /**
     * Sauvegarder le fichier TDR avec version
     */
    private function sauvegarderFichierTdr(\App\Models\Tdr $tdr, $fichier, array $data, int $version = 1): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();

        // Créer ou récupérer la structure de dossiers pour TDR
        $dossierTdr = $this->getOrCreateTdrFolderStructure($tdr->projet_id, 'tdr');

        // Hasher l'identifiant BIP pour le stockage physique
        $hashedIdentifiantBip = hash('sha256', $tdr->projet->identifiant_bip);

        // Générer un nom de fichier unique avec timestamp
        $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $nomOriginal;

        // Créer le chemin basé sur la structure de dossiers en base de données (avec hash pour stockage)
        $cheminStockage = $dossierTdr ?
            $dossierTdr->full_path :
            'projets/' . $hashedIdentifiantBip . '/Evaluation ex-ante/Etude de faisabilité/Termes de référence/Documents TDR';

        // Nettoyer le chemin pour le stockage physique (éliminer espaces et caractères spéciaux)
        $cheminStockagePhysique = strtolower(SlugHelper::generateFilePath($cheminStockage));

        // Créer le dossier s'il n'existe pas
        \Storage::disk('local')->makeDirectory($cheminStockagePhysique);
        $chemin = $fichier->storeAs($cheminStockagePhysique, $nomStockage, 'local');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => $data['resume'] ?? "Termes de référence pour l'étude de faisabilité",
            'commentaire' => $data['resume'] ?? null,
            'metadata' => [
                'type_document' => 'tdr-faisabilite',
                'tdr_id' => $tdr->id,
                'projet_id' => $tdr->projet_id,
                'version' => $version,
                'statut' => 'actif',
                'resume' => $data['resume'] ?? null,
                'tdr_faisabilite' => $data['tdr_faisabilite'] ?? null,
                'tdr_pre_faisabilite' => $data['tdr_pre_faisabilite'] ?? null,
                'type_tdr' => $data['type_tdr'] ?? 'faisabilite',
                'soumis_par' => auth()->id(),
                'soumis_le' => now()->toISOString(),
                'uploaded_context' => 'tdr-faisabilite',
                'dossier_public' => $dossierTdr ? $dossierTdr->full_path : 'Projets/' . $tdr->projet->identifiant_bip . '/Evaluation ex-ante/Etude de faisabilité/Termes de référence'
            ],
            'dossier_id' => $dossierTdr?->id,
            'fichier_attachable_id' => $tdr->id,
            'fichier_attachable_type' => \App\Models\Tdr::class,
            'categorie' => 'tdr-faisabilite',
            'ordre' => 1,
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);
    }

    /**
     * Récupérer les commentaires des évaluations antérieures
     */
    private function getCommentairesAnterieurs(Projet $projet): ?string
    {
        if ($projet->statut->value === StatutIdee::R_TDR_FAISABILITE->value) {
            $derniereEvaluation = $projet->evaluations()
                ->where('type_evaluation', 'tdr-faisabilite')
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            return $derniereEvaluation ? $derniereEvaluation->commentaire : null;
        }
        return null;
    }

    /**
     * Créer une évaluation TDR
     */
    private function creerEvaluationTdr(Tdr $tdr, array $data)
    {
        // Récupérer une évaluation en cours existante ou en créer une nouvelle pour ce TDR
        $evaluationEnCours = $tdr->evaluationEnCours();

        if (!$evaluationEnCours) {
            // Récupérer l'évaluation parent si c'est une ré-évaluation
            $evaluationParent = $tdr->evaluationParent();

            // Créer la nouvelle évaluation
            $evaluationData = [
                'type_evaluation' => 'tdr-faisabilite',
                'evaluateur_id' => auth()->id(),
                'evaluation' => [],
                'resultats_evaluation' => [],
                'date_debut_evaluation' => now(),
                'date_fin_evaluation' => isset($data['evaluer']) && $data['evaluer'] ? now() : null,
                'statut' => isset($data['evaluer']) && $data['evaluer'] ? 1 : 0, // En cours ou finalisé
                'id_evaluation' => $evaluationParent ? $evaluationParent->id : null
            ];

            $evaluationEnCours = $tdr->evaluations()->create($evaluationData);
        } else {
            // evaluer l'évaluation si demandé
            if (isset($data['evaluer']) && $data['evaluer']) {
                $evaluationEnCours->fill([
                    'date_fin_evaluation' => now(),
                    'statut' => 1
                ]);
                $evaluationEnCours->save();
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

        return $evaluationEnCours;
    }

    /**
     * Calculer le résultat d'évaluation selon les règles SFD-015
     */
    private function calculerResultatEvaluationTdr($evaluation, array $data): array
    {
        $evaluationsChamps = $data['evaluations_champs'] ?? [];

        $nombrePasse = 0;
        $nombreRetour = 0;
        $nombreNonAccepte = 0;
        $nombreNonEvalues = 0;
        $totalChamps = count($evaluationsChamps);

        // Compter les appréciations
        foreach ($evaluationsChamps as $evalChamp) {
            $appreciation = $evalChamp['appreciation'] ?? null;

            switch ($appreciation) {
                case 'passe':
                    $nombrePasse++;
                    break;
                case 'retour':
                    $nombreRetour++;
                    break;
                case 'non-accepte':
                    $nombreNonAccepte++;
                    break;
                default:
                    $nombreNonEvalues++;
                    break;
            }
        }

        // Appliquer les règles métier de SFD-015
        $resultat = $this->determinerResultatSelonRegles([
            'passe' => $nombrePasse,
            'retour' => $nombreRetour,
            'non-accepte' => $nombreNonAccepte,
            'non_evalues' => $nombreNonEvalues,
            'total' => $totalChamps
        ]);

        return array_merge($resultat, [
            'nombre_passe' => $nombrePasse,
            'nombre_retour' => $nombreRetour,
            'nombre_non_accepte' => $nombreNonAccepte,
            'nombre_non_evalues' => $nombreNonEvalues,
            'total_champs' => $totalChamps
        ]);
    }

    /**
     * Déterminer le résultat selon les règles SFD-015
     */
    private function determinerResultatSelonRegles(array $compteurs): array
    {
        // Règle 1: Si des questions n'ont pas été complétées
        if ($compteurs['non_evalues'] > 0) {
            return [
                'resultat_global' => 'non-accepte',
                'message_resultat' => 'Non accepté - Des questions n\'ont pas été complétées',
                'raison' => 'Questions non complétées'
            ];
        }

        // Règle 2: Si une réponse a été évaluée comme "Non accepté"
        if ($compteurs['non-accepte'] > 0) {
            return [
                'resultat_global' => 'non-accepte',
                'message_resultat' => 'Non accepté - Une ou plusieurs réponses évaluées comme "Non accepté"',
                'raison' => 'Réponses non acceptées'
            ];
        }

        // Règle 3: Si 10 ou plus des réponses ont été évaluées comme "Retour"
        if ($compteurs['retour'] >= 6) {
            return [
                'resultat_global' => 'non-accepte',
                'message_resultat' => 'Non accepté - Trop de retours (10 ou plus)',
                'raison' => 'Seuil de retours dépassé'
            ];
        }

        // Si toutes les réponses sont "Passe"
        if ($compteurs['passe'] === $compteurs['total'] && $compteurs['retour'] === 0) {
            return [
                'resultat_global' => 'passe',
                'message_resultat' => 'La présélection a été un succès (passes reçues dans toutes les questions)',
                'raison' => 'Toutes les questions approuvées'
            ];
        }

        // Sinon: Retour pour travail supplémentaire
        return [
            'resultat_global' => 'retour',
            'message_resultat' => 'Retour pour un travail supplémentaire (Contient des "Retours" mais pas suffisamment pour qu\'il ne soit pas accepté)',
            'raison' => 'Améliorations nécessaires'
        ];
    }

    /**
     * Traiter la décision d'évaluation automatiquement selon les règles SFD-015
     */
    private function traiterDecisionEvaluationTdrAutomatique(Projet $projet, array $resultats, null|Tdr $tdr): StatutIdee
    {
        switch ($resultats['resultat_global']) {
            case 'passe':
                // La présélection a été un succès → SoumissionRapportF (automatique)
                $projet->update([
                    'statut' => StatutIdee::SOUMISSION_RAPPORT_F,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::SOUMISSION_RAPPORT_F),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::SOUMISSION_RAPPORT_F)
                ]);

                $tdr->update([
                    'statut' => 'valide'
                ]);

                return StatutIdee::SOUMISSION_RAPPORT_F;

            case 'retour':
                // Retour pour travail supplémentaire → R_TDR_FAISABILITE (automatique)
                $projet->update([
                    'statut' => StatutIdee::R_TDR_FAISABILITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::R_TDR_FAISABILITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::R_TDR_FAISABILITE)
                ]);

                $tdr->update([
                    'statut' => 'retour_travail_supplementaire'
                ]);

                return StatutIdee::R_TDR_FAISABILITE;

            case 'non-accepte':
            default:
                // Non accepté → ATTENTE DE DÉCISION (reste à EVALUATION_TDR_F)
                $projet->update([
                    'statut' => StatutIdee::EVALUATION_TDR_F,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::EVALUATION_TDR_F),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::EVALUATION_TDR_F)
                ]);
                // L'utilisateur devra décider entre "reviser" ou "abandonner"
                return StatutIdee::EVALUATION_TDR_F;
        }
    }

    /**
     * Sauvegarder le fichier rapport de faisabilité
     */
    private function sauvegarderFichierRapport(Projet $projet, $fichier, array $data, bool $estBrouillon = false): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = 'rapport_' . $projet->id . '_' . time() . '.' . $extension;
        $chemin = $fichier->storeAs('rapports/faisabilite', $nomStockage, 'public');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => 'Rapport d\'étude de faisabilité - Cabinet: ' . ($data['cabinet_etude']['nom_cabinet'] ?? 'N/A'),
            'commentaire' => $data['recommandation'] ?? null,
            'metadata' => [
                'type_document' => 'rapport-faisabilite',
                'projet_id' => $projet->id,
                'cabinet' => [
                    'nom' => $data['cabinet_etude']['nom_cabinet'] ?? null,
                    'contact' => $data['cabinet_etude']['contact_cabinet'] ?? null,
                    'email' => $data['cabinet_etude']['email_cabinet'] ?? null,
                    'adresse' => $data['cabinet_etude']['adresse_cabinet'] ?? null
                ],
                'recommandation_adaptation' => $data['recommandation'] ?? null,
                'soumis_par' => auth()->id(),
                'soumis_le' => now()
            ],
            'fichier_attachable_id' => $projet->id,
            'fichier_attachable_type' => Projet::class,
            'categorie' => 'rapport-faisabilite',
            'ordre' => 1,
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);
    }

    /**
     * Enregistrer les informations du cabinet dans les métadonnées du projet
     */
    private function enregistrerInformationsCabinet(Projet $projet, array $data, bool $estBrouillon = false): void
    {
        // Récupérer les métadonnées existantes ou créer un nouveau tableau
        $metadata = $projet->metadata ?? [];

        // Ajouter les informations de faisabilité
        $metadata['faisabilite'] = [
            'cabinet' => [
                'nom' => $data['cabinet_etude']['nom_cabinet'] ?? null,
                'contact' => $data['cabinet_etude']['contact_cabinet'] ?? null,
                'email' => $data['cabinet_etude']['email_cabinet'] ?? null,
                'adresse_cabinet' => $data['cabinet_etude']['adresse_cabinet'] ?? null
            ],
            'recommandation_adaptation' => $data['recommandation_adaptation'] ?? null,
            'date_soumission_rapport' => now(),
            'soumis_par' => auth()->id(),
            'est_brouillon' => $estBrouillon,
            'statut' => $estBrouillon ? 'brouillon' : 'soumis',
            // Inclure les références à toutes les checklists si elles existent
            'checklists' => [
                'etude_faisabilite_marche_traitee' => isset($data['checklist_etude_faisabilite_marche']) && !empty($data['checklist_etude_faisabilite_marche']),
                'etude_faisabilite_economique_traitee' => isset($data['checklist_etude_faisabilite_economique']) && !empty($data['checklist_etude_faisabilite_economique']),
                'etude_faisabilite_technique_traitee' => isset($data['checklist_etude_faisabilite_technique']) && !empty($data['checklist_etude_faisabilite_technique']),
                'etude_faisabilite_organisationnelle_et_juridique_traitee' => isset($data['checklist_etude_faisabilite_organisationnelle_et_juridique']) && !empty($data['checklist_etude_faisabilite_organisationnelle_et_juridique']),
                'suivi_analyse_faisabilite_financiere_traitee' => isset($data['checklist_suivi_analyse_faisabilite_financiere']) && !empty($data['checklist_suivi_analyse_faisabilite_financiere']),
                'suivi_etude_analyse_impact_environnementale_et_sociale_traitee' => isset($data['checklist_suivi_etude_analyse_impact_environnementale_et_sociale']) && !empty($data['checklist_suivi_etude_analyse_impact_environnementale_et_sociale']),
                'suivi_assurance_qualite_traitee' => isset($data['checklist_suivi_assurance_qualite_rapport_etude_faisabilite']) && !empty($data['checklist_suivi_assurance_qualite_rapport_etude_faisabilite'])
            ]
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);
    }

    private function getMessageSuccesEvaluation(string $resultat): string
    {
        return match ($resultat) {
            'passe' => 'TDRs approuvés avec succès. Projet peut passer à la soumission du rapport.',
            'retour' => 'TDRs nécessitent des améliorations.',
            'non-accepte' => 'TDRs non acceptés.',
            default => 'Évaluation effectuée avec succès.'
        };
    }

    // Méthodes utilitaires du workflow (identiques à TdrfaisabiliteService)
    private function enregistrerWorkflow($projet, $nouveauStatut)
    {
        Workflow::create([
            'statut' => $nouveauStatut,
            'phase' => $this->getPhaseFromStatut($nouveauStatut),
            'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut),
            'date' => now(),
            'projetable_id' => $projet->id,
            'projetable_type' => get_class($projet),
        ]);
    }

    private function enregistrerDecision($projet, $valeur, $observations, $observateurId)
    {
        Decision::create([
            'valeur' => $valeur,
            'date' => now(),
            'observations' => $observations,
            'observateurId' => $observateurId,
            'objet_decision_id' => $projet->id,
            'objet_decision_type' => get_class($projet),
        ]);
    }

    private function getPhaseFromStatut($statut)
    {
        return match ($statut) {
            StatutIdee::TDR_FAISABILITE => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::R_TDR_FAISABILITE => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::EVALUATION_TDR_F => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::SOUMISSION_RAPPORT_F => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::VALIDATION_F => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::MATURITE => \App\Enums\PhasesIdee::selection,
            StatutIdee::PRET => \App\Enums\PhasesIdee::selection,
            StatutIdee::ABANDON => \App\Enums\PhasesIdee::evaluation_ex_tante,
            default => \App\Enums\PhasesIdee::evaluation_ex_tante,
        };
    }

    private function getSousPhaseFromStatut($statut)
    {
        return match ($statut) {
            StatutIdee::TDR_FAISABILITE => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::R_TDR_FAISABILITE => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::EVALUATION_TDR_F => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::SOUMISSION_RAPPORT_F => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::VALIDATION_F => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::MATURITE => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::PRET => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::ABANDON => \App\Enums\SousPhaseIdee::faisabilite,
            default => \App\Enums\SousPhaseIdee::faisabilite
        };
    }

    /**
     * Traiter les informations pour un projet à haut risque
     */
    private function traiterProjetHautRisque(Projet $projet, array $data): void
    {
        if (isset($data['checklist_haut_risque'])) {
            // Récupérer les métadonnées existantes ou créer un nouveau tableau
            $metadata = $projet->metadata ?? [];

            // Ajouter les informations de haut risque
            $metadata['haut_risque'] = [
                'est_a_haut_risque' => true,
                'checklist_validee' => $data['checklist_haut_risque'],
                'date_validation_checklist' => now(),
                'valide_par' => auth()->id()
            ];

            // Mettre à jour le projet
            $projet->update(['metadata' => $metadata]);
        }
    }

    /**
     * Sauvegarder les données de validation sans changer le statut
     */
    private function sauvegarderDonneesValidation(Projet $projet, array $data): void
    {
        // Récupérer les métadonnées existantes ou créer un nouveau tableau
        $metadata = $projet->metadata ?? [];

        // Ajouter les informations de validation temporaires
        $metadata['validation_faisabilite_temp'] = [
            'est_a_haut_risque' => $data['est_a_haut_risque'] ?? false,
            'commentaire' => $data['commentaire'] ?? null,
            'checklist_haut_risque' => $data['checklist_haut_risque'] ?? null,
            'date_sauvegarde' => now(),
            'sauvegarde_par' => auth()->id()
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);
    }

    /**
     * Sauvegarder les champs dynamiques basés sur le canevas
     */
    private function saveDynamicFieldsFromCanevas($tdr, array $champsData, $canevasTdr): void
    {
        // Récupérer tous les champs du canevas TDR
        $champsDefinitions = $canevasTdr->all_champs;

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
            $tdr->champs()->sync($syncData);
        }
    }

    /**
     * Gérer les documents/fichiers attachés au TDR
     */
    private function handleDocuments($tdr, array $documentsData): void
    {
        foreach ($documentsData as $index => $file) {
            if ($file) {
                // Sauvegarder le document avec la même logique que le fichier TDR
                $this->sauvegarderAutreDocument($tdr, $file, [], $index + 1);
            }
        }
    }

    /**
     * Sauvegarder un autre document avec version (même logique que sauvegarderFichierTdr)
     */
    private function sauvegarderAutreDocument($tdr, $fichier, array $data, int $ordre = 1): \App\Models\Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = "autre_document_{$ordre}.{$extension}";
        $chemin = $fichier->storeAs("tdrs/{$tdr->id}/autres-documents", $nomStockage, 'public');

        // Créer l'enregistrement Fichier
        return \App\Models\Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => $data['description'] ?? 'Autre document pour TDR de faisabilité',
            'commentaire' => $data['commentaire'] ?? null,
            'metadata' => [
                'type_document' => 'autre-document-faisabilite',
                'tdr_id' => $tdr->id,
                'projet_id' => $tdr->projet_id,
                'ordre' => $ordre,
                'statut' => 'actif',
                'soumis_par' => auth()->id(),
                'soumis_le' => now()
            ],
            'fichier_attachable_id' => $tdr->id,
            'fichier_attachable_type' => \App\Models\Tdr::class,
            'categorie' => 'tdr-faisabilite',
            'ordre' => $ordre,
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);
    }

    // Méthodes utilitaires (à implémenter selon les besoins)
    private function envoyerNotificationSoumission($projet, $fichier)
    { /* À implémenter */
    }
    private function envoyerNotificationEvaluation($projet, array $resultats)
    { /* À implémenter */
    }
    private function envoyerNotificationSoumissionRapport($projet, $fichier, array $data)
    { /* À implémenter */
    }
    private function envoyerNotificationValidation($projet, string $action, array $data)
    { /* À implémenter */
    }
    private function envoyerNotificationValidationFaisabilite($projet, string $action, array $data)
    { /* À implémenter */
    }
    private function envoyerNotificationValidationRapportFinal($projet, string $action, array $data)
    { /* À implémenter */
    }

    /**
     * Créer ou récupérer la structure de dossiers pour les TDR de faisabilité
     */
    private function getOrCreateTdrFolderStructure(int $projetId, string $type = 'tdr'): ?Dossier
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

            // 4. Sous-dossier : "Etude de faisabilité"
            $dossierEtude = Dossier::firstOrCreate([
                'nom' => 'Etude de faisabilité',
                'parent_id' => $dossierEvaluation->id
            ], [
                'nom' => 'Etude de faisabilité',
                'description' => 'Documents de l\'étude de faisabilité',
                'parent_id' => $dossierEvaluation->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#DC2626',
                'icone' => 'document-text'
            ]);

            // 5. Sous-dossier : "Termes de référence"
            $dossierTdr = Dossier::firstOrCreate([
                'nom' => 'Termes de référence',
                'parent_id' => $dossierEtude->id
            ], [
                'nom' => 'Termes de référence',
                'description' => 'Termes de référence pour l\'étude de faisabilité',
                'parent_id' => $dossierEtude->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#F59E0B',
                'icone' => 'clipboard-list'
            ]);

            // 6. Sous-dossier selon le type
            $nomSousDossier = match ($type) {
                'autres-documents' => 'Autres documents',
                'tdr' => 'Documents TDR',
                'rapports' => 'Rapports',
                default => 'Documents TDR'
            };

            $descriptionSousDossier = match ($type) {
                'autres-documents' => 'Autres documents annexes aux TDR',
                'tdr' => 'Documents des termes de référence',
                'rapports' => 'Rapports d\'étude de faisabilité',
                default => 'Documents des termes de référence'
            };

            $couleurSousDossier = match ($type) {
                'autres-documents' => '#6B7280',
                'tdr' => '#10B981',
                'rapports' => '#EF4444',
                default => '#10B981'
            };

            $iconeSousDossier = match ($type) {
                'autres-documents' => 'paper-clip',
                'tdr' => 'document',
                'rapports' => 'chart-bar',
                default => 'document'
            };

            $sousDossier = Dossier::firstOrCreate([
                'nom' => $nomSousDossier,
                'parent_id' => $dossierTdr->id
            ], [
                'nom' => $nomSousDossier,
                'description' => $descriptionSousDossier,
                'parent_id' => $dossierTdr->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => $couleurSousDossier,
                'icone' => $iconeSousDossier
            ]);

            return $sousDossier;
        } catch (Exception $e) {
            // En cas d'erreur, retourner null
            return null;
        }
    }


    /**
     * Traiter les checklists de suivi du rapport de faisabilité
     */
    private function traiterChecklistsSuiviFaisabilite($rapport, array $checklistsData, bool $estBrouillon = false, array $fichiers = []): array
    {
        try {
            DB::beginTransaction();

            // Traiter les données des 7 checklists via la relation champs() si nécessaire
            $this->traiterChampsChecklistsSuiviFaisabilite($rapport, $checklistsData);

            DB::commit();

            return [
                'success' => true,
                'message' => $estBrouillon ?
                    'Checklists de suivi sauvegardées en brouillon.' :
                    'Checklists de suivi du rapport de faisabilité validées avec succès.',
                'rapport_id' => $rapport->id,
                'projet_id' => $rapport->projet->id,
                'est_brouillon' => $estBrouillon
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement des checklists de suivi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Traiter les champs des checklists de faisabilité et les stocker dans rapport->champs()
     */
    private function traiterChampsChecklistsSuiviFaisabilite($rapport, array $checklistsData)
    {
        // Liste des 7 checklists de faisabilité
        // Mapping checklist ↔ canevasMethod
        $checklistsMap = [
            'checklist_suivi_assurance_qualite'                        => 'getCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite',
            'checklist_etude_faisabilite_technique'                    => 'getCanevasChecklisteEtudeFaisabiliteTechnique',
            'checklist_etude_faisabilite_economique'                   => 'getCanevasChecklisteEtudeFaisabiliteEconomique',
            'checklist_etude_faisabilite_marche'                       => 'getCanevasChecklisteEtudeFaisabiliteMarche',
            'checklist_etude_faisabilite_organisationnelle_juridique'  => 'getCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique',
            'checklist_suivi_analyse_faisabilite_financiere'           => 'getCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere',
            'checklist_suivi_etude_analyse_impact_environnemental_social' => 'getCanevasChecklisteSuiviEtudeImpactEnvironnementaleEtSociale',
        ];

        foreach ($checklistsMap as $checklistKey => $canevasMethod) {
            if (isset($checklistsData[$checklistKey]) && is_array($checklistsData[$checklistKey])) {
                // Construire et stocker la checklist
                $this->buildChecklist($rapport, $checklistKey, $canevasMethod, $checklistsData[$checklistKey]);
            }
        }

        $rapport->save();
        /*
        // Traiter chaque checklist
        foreach ($checklists as $checklistKey) {
            if (isset($checklistsData[$checklistKey]) && is_array($checklistsData[$checklistKey])) {
                foreach ($checklistsData[$checklistKey] as $evaluation) {
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

                // Mettre à jour checklist_suivi avec les données des champs
                $rapport->checklist_suivi[$checklistKey] = $rapport->champs->map(function ($champ) {
                    return [
                        'id' => $champ->id,
                        'label' => $champ->label,
                        'attribut' => $champ->attribut,
                        'valeur' => $champ->pivot->valeur,
                        'commentaire' => $champ->pivot->commentaire,
                        'created_at' => $champ->pivot->created_at,
                        'updated_at' => $champ->pivot->updated_at
                    ];
                });
            }
        }

        $rapport->save();
        */
    }


    /**
     * Construire et mettre à jour une checklist spécifique
     */
    private function buildChecklist($rapport, string $checklistKey, string $canevasMethod, array $evaluations)
    {
        // 1. Enregistrer ou mettre à jour les checkpoints dans champs()
        foreach ($evaluations as $evaluation) {
            $checkpointId   = $evaluation['checkpoint_id'];
            $remarque       = $evaluation['remarque'] ?? null;
            $explication    = $evaluation['explication'] ?? null;

            $rapport->champs()->syncWithoutDetaching([
                $checkpointId => [
                    'valeur'      => $remarque,
                    'commentaire' => $explication,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            ]);
        }

        // 2. Récupérer le canevas associé à la checklist
        $canevas = $this->documentRepository->{$canevasMethod}();

        // 3. Récupérer l'existant checklist_suivi
        $currentChecklist = $rapport->checklist_suivi ?? [];

        // 4. Si canevas absent → on vide la checklist
        if ($canevas === null || empty($canevas->all_champs)) {
            $currentChecklist[$checklistKey] = [];
            $rapport->checklist_suivi = $currentChecklist;
            return;
        }

        // 3. Recharger la relation
        $rapport->load('champs');

        // 5. Construire les données pour ce canevas
        $currentChecklist[$checklistKey] = collect($canevas->all_champs)->map(function ($champ) use ($rapport, $checklistKey) {
            $champRapport = $rapport->champs->firstWhere('id', $champ->id);

            return [
                'id'                 => $champ->id,
                'label'              => $champ->label,
                'attribut'           => $champ->attribut,
                'ordre_affichage'    => $champ->ordre_affichage,
                'type_champ'         => $champ->type_champ,
                'valeur'             => optional($champRapport?->pivot)->valeur,
                'commentaire'        => optional($champRapport?->pivot)->commentaire,
                'created_at'         => optional($champRapport?->pivot)->created_at,
                'updated_at'         => optional($champRapport?->pivot)->updated_at
            ];
        });

        // 6. Réassigner dans le modèle
        $rapport->checklist_suivi = $currentChecklist;
    }



    /**
     * Gérer le fichier rapport avec versioning intelligent
     */
    private function gererFichierRapportFaisabilite(Rapport $rapport, $fichier, array $data): ?Fichier
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
            'commentaire' => $data['commentaire_rapport'] ?? null,
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

    /**
     * Gérer le fichier procès verbal avec versioning intelligent
     */
    private function gererFichierProcesVerbal(Rapport $rapport, $fichier, array $data): ?Fichier
    {
        // Calculer le hash du nouveau fichier
        $nouveauHash = md5_file($fichier->getRealPath());

        // Vérifier s'il y a déjà un procès verbal avec le même hash lié à ce rapport
        $procesVerbalIdentique = $rapport->procesVerbaux()
            ->where('hash_md5', $nouveauHash)
            ->where('is_active', true)
            ->first();

        if ($procesVerbalIdentique) {
            return $procesVerbalIdentique;
        }

        // Désactiver les anciens procès verbaux de ce rapport
        $rapport->procesVerbaux()
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
            'description' => 'Procès-verbal de faisabilité',
            'commentaire' => $data['commentaire_proces_verbal'] ?? null,
            'categorie' => 'proces-verbal',
            'is_active' => true,
            'uploaded_by' => auth()->id(),
            'metadata' => [
                'type_document' => 'proces-verbal-faisabilite',
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
        $this->removeSpecificFiles($procesVerbalIdentique);

        return $fichierCree;
    }

    /**
     * Gérer le fichier procès verbal avec versioning intelligent
     */
    private function gererFichierListePresence(Rapport $rapport, $fichier, array $data): ?Fichier
    {
        // Calculer le hash du nouveau fichier
        $nouveauHash = md5_file($fichier->getRealPath());

        // Vérifier s'il y a déjà un procès verbal avec le même hash lié à ce rapport
        $listePresenceIdentique = $rapport->fichiers()->where('categorie', 'liste-presence')
            ->where('hash_md5', $nouveauHash)
            ->where('is_active', true)
            ->first();

        if ($listePresenceIdentique) {
            return $listePresenceIdentique;
        }

        // Désactiver les anciens procès verbaux de ce rapport
        $rapport->fichiers()->where('categorie', 'liste-presence')
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
            'description' => 'Procès-verbal de faisabilité',
            'commentaire' => $data['commentaire_proces_verbal'] ?? null,
            'categorie' => 'proces-verbal',
            'is_active' => true,
            'uploaded_by' => auth()->id(),
            'metadata' => [
                'type_document' => 'proces-verbal-faisabilite',
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
        $this->removeSpecificFiles($listePresenceIdentique);

        return $fichierCree;
    }

    /**
     * Supprimer une liste spécifique de fichiers
     */
    private function removeSpecificFiles($files): void
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                // Supprimer le fichier physique du storage
                if (Storage::disk('local')->exists($file->chemin)) {
                    Storage::disk('local')->delete($file->chemin);
                }

                // Supprimer l'enregistrement de la base de données
                $this->fichierRepository->delete($file->id);
            }
        }
        else{

            if ($files && isset($files->chemin)) {
                    // Supprimer le fichier physique du storage
                    if (Storage::disk('local')->exists($files->chemin)) {
                        Storage::disk('local')->delete($files->chemin);
                    }

                    // Supprimer l'enregistrement de la base de données
                    $this->fichierRepository->delete($files->id);
            }
        }
    }

    /**
     * Vérifier la cohérence du suivi rapport entre soumission et validation
     */
    private function verifierCoherenceSuiviRapport($projet, array $checklistSuiviValidation): array
    {
        try {
            // Récupérer le dernier rapport de faisabilité soumis
            $rapportFaisabilite = $projet->rapportFaisabilite()
                ->where('statut', 'soumis')
                ->latest('created_at')
                ->first();

            if (!$rapportFaisabilite) {
                return [
                    'success' => false,
                    'message' => 'Aucun rapport de faisabilité soumis trouvé pour effectuer la vérification de cohérence.'
                ];
            }

            // Récupérer les checklists de suivi du rapport de faisabilité
            $checklistsSuiviSoumission = $rapportFaisabilite->checklist_suivi;

            if (!$checklistsSuiviSoumission || !is_array($checklistsSuiviSoumission)) {
                return [
                    'success' => false,
                    'message' => 'Aucune checklist de suivi trouvée dans le rapport de faisabilité soumis.'
                ];
            }

            // Extraire la checklist d'assurance qualité spécifiquement
            $checklistSuiviSoumission = $checklistsSuiviSoumission['checklist_suivi_assurance_qualite'] ?? null;

            if (!$checklistSuiviSoumission || !is_array($checklistSuiviSoumission)) {
                return [
                    'success' => false,
                    'message' => 'Aucune checklist d\'assurance qualité trouvée dans le rapport de faisabilité soumis.'
                ];
            }

            // Comparer les checkpoints entre soumission et validation
            $incoherences = [];
            $checkpointsSoumission = collect($checklistSuiviSoumission);
            $checkpointsValidation = collect($checklistSuiviValidation);

            // Log pour debug
            \Log::info('Vérification cohérence suivi rapport faisabilité', [
                'projet_id' => $projet->id,
                'checkpoints_soumission' => $checkpointsSoumission->count(),
                'checkpoints_validation' => $checkpointsValidation->count()
            ]);

            // Vérifier que tous les checkpoints de la soumission sont présents dans la validation
            foreach ($checkpointsSoumission as $index => $checkpointSoumis) {
                $checkpointId = $checkpointSoumis['id'] ?? null;

                if (!$checkpointId) {
                    continue;
                }

                $checkpointValidation = $checkpointsValidation->firstWhere('checkpoint_id', $checkpointId);

                if (!$checkpointValidation) {
                    $incoherences[] = [
                        'type' => 'checkpoint_manquant',
                        'checkpoint_id' => $checkpointId,
                        'message' => "Le checkpoint {$checkpointId} présent lors de la soumission n'est pas trouvé dans la validation."
                    ];
                }
            }

            // Vérifier s'il y a des checkpoints supplémentaires dans la validation
            foreach ($checkpointsValidation as $checkpointValidation) {
                $checkpointId = $checkpointValidation['checkpoint_id'] ?? null;

                if (!$checkpointId) {
                    continue;
                }

                $checkpointSoumis = $checkpointsSoumission->firstWhere('id', $checkpointId);

                if (!$checkpointSoumis) {
                    $incoherences[] = [
                        'type' => 'checkpoint_supplementaire',
                        'checkpoint_id' => $checkpointId,
                        'message' => "Le checkpoint {$checkpointId} est présent dans la validation mais n'était pas dans la soumission."
                    ];
                }
            }

            // S'il y a des incohérences, retourner une erreur
            if (!empty($incoherences)) {
                \Log::warning('Incohérences détectées lors de la validation faisabilité', [
                    'projet_id' => $projet->id,
                    'nb_incoherences' => count($incoherences),
                    'incoherences' => $incoherences
                ]);

                return [
                    'success' => false,
                    'message' => 'Incohérences détectées entre le rapport soumis et les données de validation.',
                    'incoherences' => $incoherences
                ];
            }

            \Log::info('Vérification cohérence réussie faisabilité', [
                'projet_id' => $projet->id,
                'checkpoints_verifies' => $checkpointsSoumission->count()
            ]);

            return [
                'success' => true,
                'message' => 'Vérification de cohérence réussie.',
                'checkpoints_verifies' => $checkpointsSoumission->count()
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification de cohérence faisabilité', [
                'projet_id' => $projet->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification de cohérence: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier la complétude des checkpoints de validation
     */
    private function verifierCompletude(array $checklistSuiviValidation): array
    {
        try {
            $checkpointsIncomplets = [];

            foreach ($checklistSuiviValidation as $checkpoint) {
                $checkpointId = $checkpoint['checkpoint_id'] ?? null;
                $remarque = $checkpoint['remarque'] ?? null;
                $explication = $checkpoint['explication'] ?? null;

                if (!$checkpointId) {
                    continue;
                }

                // Vérifier que le checkpoint a au moins une remarque OU une explication
                if (empty($remarque) && empty($explication)) {
                    $checkpointsIncomplets[] = [
                        'checkpoint_id' => $checkpointId,
                        'message' => "Le checkpoint {$checkpointId} doit avoir au moins une remarque ou une explication."
                    ];
                }
            }

            // S'il y a des checkpoints incomplets, empêcher la validation
            if (!empty($checkpointsIncomplets)) {
                \Log::warning('Tentative de validation faisabilité avec checkpoints incomplets', [
                    'nb_checkpoints_incomplets' => count($checkpointsIncomplets),
                    'checkpoints_incomplets' => array_column($checkpointsIncomplets, 'checkpoint_id')
                ]);

                return [
                    'success' => false,
                    'message' => 'Impossible de valider le projet : ' . count($checkpointsIncomplets) . ' checkpoint(s) sont incomplets. Tous les checkpoints doivent avoir au moins une remarque ou une explication.',
                    'checkpoints_incomplets' => $checkpointsIncomplets
                ];
            }

            \Log::info('Tous les checkpoints faisabilité sont complétés', [
                'nb_checkpoints_verifies' => count($checklistSuiviValidation)
            ]);

            return [
                'success' => true,
                'message' => 'Tous les checkpoints sont complétés.',
                'nb_checkpoints_complets' => count($checklistSuiviValidation)
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification de complétude des checkpoints faisabilité', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification de complétude: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Attacher le fichier rapport de validation de l'étude de faisabilité
     */
    private function attacherFichierRapportValidation($projet, $fichier, $evaluation)
    {
        if (!$fichier instanceof \Illuminate\Http\UploadedFile) {
            return null;
        }

        // Hasher l'identifiant BIP selon le pattern projets/{hash_identifiant_bip}/Evaluation-ex-ante/etude_faisabilite/rapport_validation
        $hashedIdentifiantBip = hash('sha256', $projet->identifiant_bip);

        // Générer un nom de fichier unique
        $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $fichier->hashName();

        // Stocker le fichier selon le pattern de hash avec structure Evaluation-ex-ante
        $path = $fichier->storeAs(
            "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/etude_faisabilite/rapport-validation",
            $nomStockage,
            'local'
        );

        // Créer l'enregistrement du fichier via la relation polymorphe
        $fichierCree = $projet->fichiers()->create([
            'nom_original' => $fichier->getClientOriginalName(),
            'nom_stockage' => $nomStockage,
            'chemin' => $path,
            'extension' => $fichier->getClientOriginalExtension(),
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => 'Rapport de validation de l\'étude de faisabilité',
            'commentaire' => 'Document de validation soumis par le Comité de validation',
            'categorie' => 'rapport-validation-faisabilite',
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true,
            'metadata' => [
                'evaluation_id' => $evaluation->id,
                'type_validation' => 'etude-faisabilite',
                'action_validation' => $evaluation->resultats_evaluation,
                'uploaded_context' => 'validation-etude-faisabilite',
                'soumis_par' => auth()->id(),
                'soumis_le' => now()->toISOString(),
                'folder_structure' => "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/etude_faisabilite/rapport_validation"
            ]
        ]);

        return $fichierCree;
    }
}
