<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Models\Fichier;
use App\Models\Projet;
use App\Models\Decision;
use App\Models\Workflow;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use App\Http\Resources\projets\ProjetResource;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\Contracts\TdrFaisabiliteServiceInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TdrFaisabiliteService extends BaseService implements TdrFaisabiliteServiceInterface
{
    protected DocumentRepositoryInterface $documentRepository;
    protected ProjetRepositoryInterface $projetRepository;
    protected EvaluationRepositoryInterface $evaluationRepository;

    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        ProjetRepositoryInterface $projetRepository,
        EvaluationRepositoryInterface $evaluationRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->projetRepository = $projetRepository;
        $this->evaluationRepository = $evaluationRepository;
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
     * Soumettre les TDRs de faisabilité (SFD-014)
     */
    public function soumettreTdrs(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DPAF uniquement)
            if (!in_array(auth()->user()->type, ['dpaf', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette soumission.'
                ], 403);
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

            // Traitement et sauvegarde du fichier TDR
            $fichierTdr = null;

            if (isset($data['tdr'])) {
                $fichierTdr = $this->sauvegarderFichierTdr($projet, $data['tdr'], $data);
            }

            // Récupérer les commentaires des évaluations antérieures si c'est un retour
            $commentairesAnterieurs = $this->getCommentairesAnterieurs($projet);

            $projet->resume_tdr_faisabilite = $data["resume_tdr_faisabilite"];

            // Changer le statut du projet
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

            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationSoumission($projet, $fichierTdr);

            return response()->json([
                'success' => true,
                'message' => 'TDRs de faisabilité soumis avec succès.',
                'data' => [
                    'projet' => new ProjetResource($projet),
                    'fichier_id' => $fichierTdr ? $fichierTdr->id : null,
                    'projet_id' => $projet->id,
                    'ancien_statut' => in_array($projet->statut->value, [StatutIdee::TDR_FAISABILITE->value, StatutIdee::R_TDR_FAISABILITE->value]) ? $projet->statut->value : StatutIdee::TDR_FAISABILITE->value,
                    'nouveau_statut' => StatutIdee::EVALUATION_TDR_F->value,
                    'fichier_url' => $fichierTdr ? $fichierTdr->url : null,
                    'resume' => $data['resume'] ?? null,
                    'tdr_faisabilite' => $data['tdr_faisabilite'] ?? null,
                    'type_tdr' => $data['type_tdr'] ?? null,
                    'soumis_par' => auth()->id(),
                    'soumis_le' => now()->format('d/m/Y H:i:s'),
                    'commentaires_anterieurs' => $commentairesAnterieurs
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
            if (in_array(auth()->user()->type, ['dgpd', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette évaluation.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier qu'il y a des TDRs soumis
            $tdrsFichiers = $projet->fichiersParCategorie('tdr-faisabilite')->get();

            if ($tdrsFichiers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun TDR trouvé pour ce projet.'
                ], 404);
            }

            // Créer ou mettre à jour l'évaluation
            $evaluation = $this->creerEvaluationTdr($projet, $data);

            // Calculer le résultat de l'évaluation selon les règles SFD-015
            $resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, $data);

            // Traiter la décision selon le résultat (changement automatique du statut)
            $nouveauStatut = $this->traiterDecisionEvaluationTdrAutomatique($projet, $resultatsEvaluation);

            // Préparer l'évaluation complète pour enregistrement
            $evaluationComplete = [
                'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationTdr()->all_champs)->map(function ($champ) use ($evaluation) {
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
            // Vérifier les autorisations (DGPD uniquement)
            if (in_array(auth()->user()->type, ['dgpd', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour consulter cette évaluation.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Récupérer les TDRs soumis
            $tdrsFichiers = $projet->fichiersParCategorie('tdr-faisabilite')->get();
            if ($tdrsFichiers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun TDR trouvé pour ce projet.'
                ], 404);
            }

            // Récupérer l'évaluation en cours ou la dernière évaluation
            $evaluation = $projet->evaluations()
                ->where('type_evaluation', 'tdr-faisabilite')
                ->with(['champs_evalue' => function ($query) {
                    $query->orderBy('ordre_affichage');
                }])
                ->orderBy('created_at', 'desc')
                ->first();

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
                        'attribut' => isset($champ["label"]) ? $champ["attribut"] : null,
                        'type_champ' => isset($champ["type_champ"]) ? $champ["type_champ"] : "textearea",
                        'ordre_affichage' => isset($champ["ordre_affichage"]) ? $champ["ordre_affichage"] : 0,
                        'appreciation' =>  isset($champ["appreciation"]) ? $champ["appreciation"] : null,
                        'commentaire_evaluateur' =>  isset($champ["commentaire_evaluateur"]) ? $champ["commentaire_evaluateur"] : null,
                        'date_appreciation' =>  isset($champ["date_evaluation"]) ? $champ["date_evaluation"] : null,
                    ];
                }
            } else {

                // Récupérer le canevas d'appréciation des TDRs
                $canevasAppreciation = $this->documentRepository->getModel()
                    ->where('type', 'formulaire')
                    ->where('slug', 'canevas-appreciation-tdr')
                    ->with(['champs' => function ($query) {
                        $query->orderBy('ordre_affichage');
                    }])
                    ->first();

                if (!$canevasAppreciation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Canevas d\'appréciation des TDRs introuvable.'
                    ], 404);
                }

                foreach ($canevasAppreciation->champs as $champ) {
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
                        'date_evaluation' => $evaluationExistante ? $evaluationExistante->pivot->date_note : null
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
                        'attribut' => isset($champ["label"]) ? $champ["attribut"] : null,
                        'type_champ' => isset($champ["type_champ"]) ? $champ["type_champ"] : "textearea",
                        'ordre_affichage' => isset($champ["ordre_affichage"]) ? $champ["ordre_affichage"] : 0,
                        'appreciation' =>  isset($champ["appreciation"]) ? $champ["appreciation"] : null,
                        'commentaire_evaluateur' =>  isset($champ["commentaire_evaluateur"]) ? $champ["commentaire_evaluateur"] : null,
                        'date_appreciation' =>  isset($champ["date_evaluation"]) ? $champ["date_evaluation"] : null,
                    ];
                }
                $resultatsEvaluation = $evaluation->resultats_evaluation;
            } else {
                if ($evaluation) {
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
            }

            // Déterminer les actions suivantes selon le résultat
            if ($resultatsEvaluation) {
                $actionsSuivantes = $this->getActionsSuivantesSelonResultat($resultatsEvaluation['resultat_global']);
            }

            // Récupérer toutes les évaluations du projet pour ce type
            $evaluations = $projet->evaluations()
                ->where('statut', 1)
                ->where('id', "<>", $evaluation ? $evaluation->id : 0)
                ->where('type_evaluation', 'tdr-faisabilite')
                ->with(['champs_evalue' => function ($query) {
                    $query->orderBy('ordre_affichage');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            // Construire l'historique des évaluations
            $historiqueEvaluations = $evaluations->map(function ($evaluation) {
                $resultatsEvaluation = $evaluation->resultats_evaluation;
                $champs_evalues = is_string($evaluation->evaluation) ? json_decode($evaluation->evaluation)->champs_evalues : $evaluation->evaluation;
                return [
                    'id' => $evaluation->id,
                    'statut' => $evaluation->statut,
                    'evaluateur' => $evaluation->evaluateur ? new UserResource($evaluation->evaluateur) : 'N/A',
                    'date_debut' => Carbon::parse($evaluation->date_debut_evaluation)->format("Y-m-d h:i:s"),
                    'date_fin' => Carbon::parse($evaluation->date_fin_evaluation)->format("Y-m-d h:i:s"),
                    'commentaire_global' => $evaluation->commentaire,
                    'resultat_global' => $resultatsEvaluation['resultat_global'] ?? null,
                    'message_resultat' => $resultatsEvaluation['message_resultat'] ?? null,
                    'champs_evalues' => collect($champs_evalues)->map(function ($champ) {
                        $champInter = (array)$champ;
                        $champ =  (array)$champ;
                        return [
                            'champ_id' => isset($champ["champ_id"]) ? $champ["champ_id"] : null,
                            'label' => isset($champ["label"]) ? $champ["label"] : null,
                            'attribut' => isset($champ["label"]) ? $champ["attribut"] : null,
                            'type_champ' =>  isset($champ["type_champ"]) ? $champ["type_champ"] : "textearea",
                            'ordre_affichage' => isset($champ["ordre_affichage"]) ? $champ["ordre_affichage"] : 0,
                            'appreciation' =>  isset($champ["appreciation"]) ? $champ["appreciation"] : null,
                            'commentaire_evaluateur' =>  isset($champ["commentaire_evaluateur"]) ? $champ["commentaire_evaluateur"] : null,
                            'date_appreciation' =>  isset($champ["date_evaluation"]) ? $champ["date_evaluation"] : null,
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Détails de l\'évaluation TDR de faisabilité récupérés avec succès.',
                'data' => [
                    'projet' => new ProjetResource($projet),
                    'tdr' => $tdrsFichiers->map(function ($fichier) {
                        return [
                            'id' => $fichier->id,
                            'nom_original' => $fichier->nom_original,
                            'url' => $fichier->url,
                            'taille' => $fichier->taille,
                            'date_upload' => $fichier->created_at,
                            'resume' => $fichier->metadata['resume'] ?? $fichier->description,
                            'type_tdr' => $fichier->metadata['type_tdr'] ?? 'faisabilite'
                        ];
                    }),
                    'resume_tdr' => $projet->resume_tdr_faisabilite,
                    'evaluation_existante' => $evaluation ? [
                        'id' => $evaluation->id,
                        'statut' => $evaluation->statut,
                        'evaluateur' => new UserResource($evaluation->evaluateur),
                        'date_debut' => Carbon::parse($evaluation->date_debut_evaluation)->format("Y-m-d h:i:s"),
                        'date_fin' => Carbon::parse($evaluation->date_fin_evaluation)->format("Y-m-d h:i:s"),
                        'commentaire_global' => $evaluation->commentaire,
                        'grille_evaluation' => $grilleEvaluation,
                    ] : null,
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
            if (in_array(auth()->user()->type, ['dgpd', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette validation.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::EVALUATION_TDR_F->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape d\'évaluation des TDRs.'
                ], 422);
            }

            // Vérifier qu'il y a une évaluation terminée avec résultat "non accepté"
            $evaluation = $projet->evaluations()
                ->where('type_evaluation', 'tdr-faisabilite')
                ->where('statut', 1)
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

            if ($resultatsEvaluation['resultat_global'] !== 'non_accepte') {
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
    public function getDetailsValidation(int $projetId): JsonResponse
    {
        try {
            // Vérifier les autorisations (DGPD uniquement)
            if (in_array(auth()->user()->type, ['dgpd', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour consulter ces détails.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est à l'étape d'évaluation ou post-évaluation
            if (!in_array($projet->statut->value, [
                StatutIdee::EVALUATION_TDR_F->value,
                StatutIdee::VALIDATION_F->value,
                StatutIdee::SOUMISSION_RAPPORT_F->value,
                StatutIdee::R_TDR_FAISABILITE->value,
                StatutIdee::TDR_FAISABILITE->value,
                StatutIdee::ABANDON->value
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à une étape permettant la consultation des détails de validation.'
                ], 422);
            }

            // Récupérer l'évaluation en cours ou la dernière évaluation
            $evaluation = $projet->evaluations()
                ->where('type_evaluation', 'tdr-faisabilite')
                ->with(['champs_evalue' => function ($query) {
                    $query->orderBy('ordre_affichage');
                }])
                ->orderBy('created_at', 'desc')
                ->first();

            // Récupérer les décisions liées aux TDRs
            $decisions = $projet->decisions()
                ->where('valeur', 'like', '%TDR%')
                ->orderBy('created_at', 'desc')
                ->get();

            // Récupérer les fichiers TDR
            $tdrsFichiers = $projet->fichiersParCategorie('tdr-faisabilite')->get();

            // Construire l'historique des décisions
            $historiqueDecisions = $decisions->map(function ($decision) {
                return [
                    'id' => $decision->id,
                    'valeur' => $decision->valeur,
                    'observations' => $decision->observations,
                    'date' => Carbon::parse($decision->date)->format("Y-m-d h:i:s"),
                    'observateur_id' => $decision->observateur_id,
                    'observateur_nom' => $decision->observateur->nom ?? 'N/A',
                    'observateur_prenom' => $decision->observateur->prenom ?? 'N/A'
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Détails de validation des TDRs récupérés avec succès.',
                'data' => [
                    'projet' => new ProjetResource($projet),
                    'resume_tdr' => $projet->resume_tdr_faisabilite,
                    'historique_decisions' => $historiqueDecisions,
                    'statut_actuel' => [
                        'code' => $projet->statut->value,
                        'label' => $projet->statut->name,
                        'phase' => $projet->phase,
                        'sous_phase' => $projet->sous_phase
                    ],
                    'actions_possibles' => $this->getActionsPossibles($projet->statut, $evaluation['resultat_global'] ?? null)
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

            // Vérifier les autorisations (DPAF uniquement)
            if (in_array(auth()->user()->type, ['dpaf', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette soumission.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Traitement et sauvegarde du fichier rapport
            $fichierRapport = null;
            if (isset($data['rapport_faisabilite'])) {
                $fichierRapport = $this->sauvegarderFichierRapport($projet, $data['rapport_faisabilite'], $data);
            }

            if (isset($data['rapport_couts_avantages'])) {
                $fichierRapport = $this->sauvegarderFichierRapport($projet, $data['rapport_couts_avantages'], $data);
            }


            // Enregistrer les informations du cabinet et recommandations
            $this->enregistrerInformationsCabinet($projet, $data);

            // Changer le statut du projet
            $projet->update([
                'statut' => StatutIdee::VALIDATION_F,
                'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_F),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_F)
            ]);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, StatutIdee::VALIDATION_F);
            $this->enregistrerDecision(
                $projet,
                "Soumission du rapport de faisabilité",
                "Rapport soumis par cabinet: " . ($data['cabinet_etude']['nom_cabinet'] ?? 'N/A'),
                auth()->user()->personne->id
            );

            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationSoumissionRapport($projet, $fichierRapport, $data);

            return response()->json([
                'success' => true,
                'message' => 'Rapport de faisabilité soumis avec succès.',
                'data' => [
                    'fichier_id' => $fichierRapport ? $fichierRapport->id : null,
                    'projet_id' => $projet->id,
                    'ancien_statut' => StatutIdee::SOUMISSION_RAPPORT_F->value,
                    'nouveau_statut' => StatutIdee::VALIDATION_F->value,
                    'cabinet' => [
                        'nom' => $data['cabinet_etude']['nom_cabinet'] ?? null,
                        'contact' => $data['cabinet_etude']['contact_cabinet'] ?? null,
                        'email' => $data['cabinet_etude']['email_cabinet'] ?? null,
                        'adresse_cabinet' => $data['cabinet_etude']['adresse_cabinet'] ?? null
                    ],
                    'recommandation' => $data['recommandation'] ?? null,
                    'fichier_url' => $fichierRapport ? $fichierRapport->url : null,
                    'soumis_par' => auth()->id(),
                    'soumis_le' => now()->format('d/m/Y H:i:s')
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
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
            if (in_array(auth()->user()->type, ['dgpd', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette validation.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::VALIDATION_F->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de validation de faisabilité.'
                ], 422);
            }

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

            // Traiter les informations du projet à haut risque si applicable
            if (isset($data['est_haut_risque']) && $data['est_haut_risque']) {
                $this->traiterProjetHautRisque($projet, $data);
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
                    'est_haut_risque' => $data['est_haut_risque'] ?? false,
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

    // === MÉTHODES UTILITAIRES (identiques à TdrPrefaisabiliteService) ===

    /**
     * Obtenir les actions possibles selon le statut et le résultat d'évaluation
     */
    private function getActionsPossibles($statut, $resultatEvaluation = null): array
    {
        return match ($statut) {
            StatutIdee::EVALUATION_TDR_F => [
                'evaluer' => 'Procéder à l\'évaluation des TDRs',
                // Actions de décision finale seulement pour cas "non accepté"
                ...(($resultatEvaluation === 'non_accepte') ? [
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
            'non_accepte' => [
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
     * Sauvegarder le fichier TDR téléversé
     */
    private function sauvegarderFichierTdr(Projet $projet, $fichier, array $data): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = 'tdr_faisabilite_' . $projet->id . '_' . time() . '.' . $extension;
        $chemin = $fichier->storeAs('tdrs/faisabilite', $nomStockage, 'public');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => $data['resume'] ?? 'Termes de référence pour l\'étude de faisabilité',
            'commentaire' => $data['resume'] ?? null,
            'metadata' => [
                'type_document' => 'tdr-faisabilite',
                'projet_id' => $projet->id,
                'resume' => $data['resume'] ?? null,
                'tdr_faisabilite' => $data['tdr_faisabilite'] ?? null,
                'type_tdr' => $data['type_tdr'] ?? 'faisabilite',
                'soumis_par' => auth()->id(),
                'soumis_le' => now()
            ],
            'fichier_attachable_id' => $projet->id,
            'fichier_attachable_type' => Projet::class,
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
    private function creerEvaluationTdr(Projet $projet, array $data)
    {
        // Récupérer une évaluation en cours existante ou en créer une nouvelle
        $evaluation = $projet->evaluations()
            ->where('type_evaluation', 'tdr-faisabilite')
            ->where('statut', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$evaluation) {
            // Récupérer l'évaluation parent si c'est une ré-évaluation
            $evaluationParent = $projet->evaluations()
                ->where('type_evaluation', 'tdr-faisabilite')
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            $evaluation = $projet->evaluations()->create([
                'type_evaluation' => 'tdr-faisabilite',
                'evaluateur_id' => auth()->id(),
                'evaluation' => [],
                'resultats_evaluation' => [],
                'date_debut_evaluation' => now(),
                'statut' => 0, // En cours
                'id_evaluation' => $evaluationParent ? $evaluationParent->id : null
            ]);
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

            $evaluation->champs_evalue()->syncWithoutDetaching($syncData);
        }

        // Finaliser l'évaluation
        $evaluation->update([
            'date_fin_evaluation' => now(),
            'statut' => 1,
            'commentaire' => $data['commentaire'] ?? null
        ]);

        return $evaluation;
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
                case 'non_accepte':
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
            'non_accepte' => $nombreNonAccepte,
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
                'resultat_global' => 'non_accepte',
                'message_resultat' => 'Non accepté - Des questions n\'ont pas été complétées',
                'raison' => 'Questions non complétées'
            ];
        }

        // Règle 2: Si une réponse a été évaluée comme "Non accepté"
        if ($compteurs['non_accepte'] > 0) {
            return [
                'resultat_global' => 'non_accepte',
                'message_resultat' => 'Non accepté - Une ou plusieurs réponses évaluées comme "Non accepté"',
                'raison' => 'Réponses non acceptées'
            ];
        }

        // Règle 3: Si 10 ou plus des réponses ont été évaluées comme "Retour"
        if ($compteurs['retour'] >= 10) {
            return [
                'resultat_global' => 'non_accepte',
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
    private function traiterDecisionEvaluationTdrAutomatique(Projet $projet, array $resultats): StatutIdee
    {
        switch ($resultats['resultat_global']) {
            case 'passe':
                // La présélection a été un succès → SoumissionRapportF (automatique)
                $projet->update([
                    'statut' => StatutIdee::SOUMISSION_RAPPORT_F,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::SOUMISSION_RAPPORT_F),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::SOUMISSION_RAPPORT_F)
                ]);
                return StatutIdee::SOUMISSION_RAPPORT_F;

            case 'retour':
                // Retour pour travail supplémentaire → R_TDR_FAISABILITE (automatique)
                $projet->update([
                    'statut' => StatutIdee::R_TDR_FAISABILITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::R_TDR_FAISABILITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::R_TDR_FAISABILITE)
                ]);
                return StatutIdee::R_TDR_FAISABILITE;

            case 'non_accepte':
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
    private function sauvegarderFichierRapport(Projet $projet, $fichier, array $data): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = 'rapport_faisabilite_' . $projet->id . '_' . time() . '.' . $extension;
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
    private function enregistrerInformationsCabinet(Projet $projet, array $data): void
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
            'soumis_par' => auth()->id()
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);
    }

    private function getMessageSuccesEvaluation(string $resultat): string
    {
        return match ($resultat) {
            'passe' => 'TDRs approuvés avec succès. Projet peut passer à la soumission du rapport.',
            'retour' => 'TDRs nécessitent des améliorations.',
            'non_accepte' => 'TDRs non acceptés.',
            default => 'Évaluation effectuée avec succès.'
        };
    }

    // Méthodes utilitaires du workflow (identiques à TdrPrefaisabiliteService)
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
                'est_haut_risque' => true,
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
            'est_haut_risque' => $data['est_haut_risque'] ?? false,
            'commentaire' => $data['commentaire'] ?? null,
            'checklist_haut_risque' => $data['checklist_haut_risque'] ?? null,
            'date_sauvegarde' => now(),
            'sauvegarde_par' => auth()->id()
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);
    }

    // Méthodes utilitaires (à implémenter selon les besoins)
    private function envoyerNotificationSoumission($projet, $fichier)
    { /* À implémenter */ }
    private function envoyerNotificationEvaluation($projet, array $resultats)
    { /* À implémenter */ }
    private function envoyerNotificationSoumissionRapport($projet, $fichier, array $data)
    { /* À implémenter */ }
    private function envoyerNotificationValidation($projet, string $action, array $data)
    { /* À implémenter */ }
    private function envoyerNotificationValidationFaisabilite($projet, string $action, array $data)
    { /* À implémenter */ }
}
