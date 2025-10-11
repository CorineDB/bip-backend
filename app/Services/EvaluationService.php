<?php

namespace App\Services;

use App\Enums\StatutIdee;
use App\Http\Resources\EvaluationCritereResource;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\EvaluationResource;
use App\Models\CategorieCritere;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Repositories\Contracts\IdeeProjetRepositoryInterface;
use App\Services\Contracts\EvaluationServiceInterface;
use App\Models\Evaluation;
use App\Models\EvaluationCritere;
use App\Models\Critere;
use App\Models\Dgpd;
use App\Models\Dpaf;
use App\Models\Notation;
use App\Models\Organisation;
use App\Models\User;
use App\Models\Workflow;
use App\Models\Decision;
use App\Notifications\EvaluationClimatiqueFinaliseeNotification;
use App\Notifications\ProgressionEvaluationClimatiqueNotification;
use App\Notifications\EvaluationClimatiqueTermineNotification;
use App\Notifications\NouvelleIdeeProjetNotification;
use App\Notifications\DecisionFaibleScoreClimatiqueNotification;
use App\Notifications\ValidationResponsableHierarchiqueNotification;
use App\Notifications\DemandeAnalyseMulticriteresNotification;
use App\Notifications\ResultatAMCNotification;
use App\Notifications\FinAMCAnalysteNotification;
use App\Notifications\ComiteValidationMinisterielNotification;
use App\Notifications\DecisionFinaleValidationNotification;
use App\Traits\GenerateUniqueId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use App\Events\IdeeProjetTransformee;
use App\Http\Resources\CategorieCritereResource;
use App\Http\Resources\idees_projet\IdeesProjetResource;
use App\Http\Resources\UserResource;
use App\Models\IdeeProjet;
use App\Repositories\Contracts\CategorieCritereRepositoryInterface;

class EvaluationService extends BaseService implements EvaluationServiceInterface
{
    use GenerateUniqueId;
    protected BaseRepositoryInterface $repository;
    protected CategorieCritereRepositoryInterface $categorieCritereRepository;
    protected IdeeProjetRepositoryInterface $ideeProjetRepository;

    public function __construct(
        EvaluationRepositoryInterface $repository,
        IdeeProjetRepositoryInterface $ideeProjetRepository,
        CategorieCritereRepositoryInterface $categorieCritereRepository
    ) {
        parent::__construct($repository);
        $this->categorieCritereRepository = $categorieCritereRepository;
        $this->ideeProjetRepository = $ideeProjetRepository;
    }

    protected function getResourceClass(): string
    {
        return EvaluationResource::class;
    }

    /**
     * Finalize evaluation and calculate final results.
     */
    public function validerIdeeDeProjet($ideeProjetId, array $attributs): JsonResponse
    {
        try {

            if (!auth()->user()->hasPermissionTo('valider-une-idee-de-projet-en-interne') && auth()->user()->type !== "dpaf") {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            if ($ideeProjet->statut != StatutIdee::IDEE_DE_PROJET) {
                throw new Exception("Vous le statut de l'idee de projet est a brouillon");
            }

            // Validation idee de projet
            $evaluation = Evaluation::create([
                'type_evaluation' => 'validation-idee-projet',
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
                'statut' => 1
            ]);

            // Vérifier que l'évaluation climatique existe
            $evaluationClimatique = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            $criteresEvaluationClimatique = $evaluationClimatique->evaluationCriteres()
                ->autoEvaluation()
                ->active();

            if ($attributs["decision"] == "valider") {
                $ideeProjet->update([
                    'statut' => StatutIdee::ANALYSE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::ANALYSE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::ANALYSE),
                ]);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($ideeProjet, StatutIdee::ANALYSE);
                $this->enregistrerDecision($ideeProjet, 'Validation par Responsable hiérarchique', $attributs["commentaire"] ?? 'Idée validée pour analyse multicritères');

                $criteresEvaluationClimatique = $criteresEvaluationClimatique->byEvaluateur($evaluationClimatique->id)
                    ->with(['critere', 'notation', 'categorieCritere', 'evaluateur'])
                    ->get();

                $evaluationClimatique->update([
                    'resultats_evaluation' => [],
                    'evaluation' => EvaluationCritereResource::collection($criteresEvaluationClimatique),
                    'valider_le' => null,
                    'statut' => 1  // Marquer comme terminée
                ]);
            } else {

                $ideeProjet->update([
                    'est_soumise' => false,
                    //'score_climatique' => 0,
                    'statut' => StatutIdee::BROUILLON,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::BROUILLON),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::BROUILLON),
                ]);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($ideeProjet, StatutIdee::BROUILLON);
                $this->enregistrerDecision($ideeProjet, 'Rejet par Responsable hiérarchique', $attributs["commentaire"] ?? 'Idée rejetée - Retour en phase de rédaction');

                // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
                $evaluateurs = $evaluation->evaluateursClimatique()->get();

                // Récupérer les critères éligibles pour l'évaluation climatique
                $criteres = Critere::where(function ($query) {
                    $query->whereHas('categorie_critere', function ($subQuery) {
                        $subQuery->where('slug', 'evaluation-preliminaire-multi-projet-impact-climatique');
                    })->orWhere(function ($subQuery) {
                        $subQuery->whereNull('categorie_critere_id')
                            ->where('is_mandatory', true);
                    });
                })->get();

                // Assigner chaque évaluateur à tous les critères
                foreach ($evaluateurs as $evaluateur) {
                    foreach ($criteres as $critere) {
                        EvaluationCritere::create([
                            'evaluation_id' => $evaluation->id,
                            'critere_id' => $critere->id,
                            'evaluateur_id' => $evaluateur->id,
                            'categorie_critere_id' => $critere->categorie_critere_id,
                            'note' => 'En attente',
                            'notation_id' => null,
                            'is_auto_evaluation' => true,
                            'est_archiver' => false
                        ]);
                    }
                }

                $evaluationClimatique->update([
                    'resultats_evaluation' => [],
                    'evaluation' => [],
                    'valider_le' => null,
                    'statut' => 0  // Marquer comme terminée
                ]);

                $criteresEvaluationClimatique->get()->each->update(["est_archiver" => true]);
            }

            $ideeProjet->refresh();

            DB::commit();

            // Notifier le Responsable projet de la décision
            $responsableProjet = $ideeProjet->responsable;
            if ($responsableProjet) {
                $decision = $attributs["decision"];
                Notification::send($responsableProjet, new ValidationResponsableHierarchiqueNotification($ideeProjet, $decision, $attributs["commentaire"] ?? null));

                // Si validé, notifier l'analyste DGPD et les membres du Service technique
                if ($decision === 'valider') {
                    $analystesDGPD = User::where('type', 'analyste-dgpd')->get();
                    $servicesTechniques = User::whereHas('roles', function ($query) {
                        $query->whereIn('slug', ['service-technique', 'service-etude']);
                    })->get();

                    $destinataires = $analystesDGPD->merge($servicesTechniques);
                    if ($destinataires->count() > 0) {
                        Notification::send($destinataires, new DemandeAnalyseMulticriteresNotification($ideeProjet, auth()->user()));
                    }
                }
            }

            // Récupérer les décisions de validation
            $decisions = $ideeProjet->decisions()->with('observateur')->get();

            return response()->json([
                'success' => true,
                'message' => 'Idee de projet evaluer avec succès',
                'data' => $evaluation,
                'decisions' => $decisions
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la validation de l'idee de projet. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Récupérer les décisions de validation d'idée de projet par responsable hiérarchique
     */
    public function getDecisionsValiderIdeeDeProjet($ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->profilable_type == Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acceder a cette resource", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer les évaluations de validation par responsable hiérarchique
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'validation-idee-projet')
                ->whereNotNull('valider_par')
                ->whereNotNull('valider_le')
                ->where('statut', 1)
                ->with(['validator', 'commentaires'])
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'idee_projet' => new IdeesProjetResource($ideeProjet),
                    'evaluation' => $evaluation ? [
                        'id' => $evaluation->id,
                        'valider_le' => Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i"),
                        'valider_par' => new UserResource($evaluation->validator),
                        'decision' => $evaluation->evaluation,
                        'statut' => $evaluation->statut
                    ] : null
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la récupération des décisions. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }
    /**
     * Finalize evaluation and calculate final results.
     */
    public function validationIdeeDeProjetAProjet($ideeProjetId, array $attributs): JsonResponse
    {
        try {
            if (!auth()->user()->hasPermissionTo('valider-une-idee-de-projet-a-projet') && auth()->user()->type !== "analyste-dgpd") {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if ($ideeProjet->statut->value != StatutIdee::VALIDATION->value) {
                throw new Exception("L'idee de projet n'est pas a l'etape de validation");
            }

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
            ]);

            if ($attributs["decision"] == "valider") {
                $ideeProjet->update([
                    'statut' => StatutIdee::NOTE_CONCEPTUEL,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::NOTE_CONCEPTUEL),
                ]);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($ideeProjet, StatutIdee::NOTE_CONCEPTUEL);
                $this->enregistrerDecision($ideeProjet, 'Validation finale par analyste DGPD', $attributs["commentaire"] ?? 'Idée transformée en projet');

                // Déclencher l'event pour dupliquer vers un projet seulement si validé
                event(new IdeeProjetTransformee($ideeProjet));
                //IdeeProjetTransformee::dispatch($ideeProjet);
            } else {
                $ideeProjet->update([
                    'score_amc' => 0,
                    'statut' => StatutIdee::ANALYSE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::ANALYSE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::ANALYSE),
                ]);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($ideeProjet, StatutIdee::ANALYSE);
                $this->enregistrerDecision($ideeProjet, 'Rejet par analyste DGPD', $attributs["commentaire"] ?? 'Idée rejetée - Retour en analyse');

                /* $evaluation->update([
                    'resultats_evaluation' => [],
                    'evaluation' => [],
                    'valider_le' => null,
                    'statut' => -1  // Marquer comme terminée
                ]); */

                // Vérifier que l'évaluation climatique existe
                $evaluationClimatique = Evaluation::where('projetable_type', get_class($ideeProjet))
                    ->where('projetable_id', $ideeProjet->id)
                    ->where('type_evaluation', 'climatique')
                    ->firstOrFail();

                $evaluationClimatique->update([
                    'date_fin_evaluation' => null
                ]);

                // Vérifier que l'évaluation amc existe
                $evaluationAMC = Evaluation::where('projetable_type', get_class($ideeProjet))
                    ->where('projetable_id', $ideeProjet->id)
                    ->where('type_evaluation', 'amc')
                    ->firstOrFail();

                $criteresEvaluationAMC = $evaluationAMC->evaluationCriteres()
                    ->evaluationExterne()
                    ->active()->get();

                $criteresEvaluationClimatique = $evaluationClimatique->evaluationCriteres()
                    ->evaluationExterne()
                    ->active()->get();

                $criteresEvaluationAMC->each->update(["est_archiver" => true]);
                $criteresEvaluationClimatique->each->update(["est_archiver" => true]);
            }

            DB::commit();

            $ideeProjet->refresh();

            // Récupérer le responsable hiérarchique qui a validé l'idée en interne
            $evaluationValidation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'validation-idee-projet')
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            // Notifier le Responsable hiérarchique et projet de la décision finale
            $responsableProjet = $ideeProjet->responsable;
            $destinatairesDecision = collect([$responsableProjet]);

            if ($evaluationValidation && $evaluationValidation->evaluateur) {
                $destinatairesDecision->push($evaluationValidation->evaluateur);
            }

            $decision = $attributs["decision"];
            Notification::send($destinatairesDecision, new DecisionFinaleValidationNotification($ideeProjet, $decision, auth()->user(), $attributs["commentaire"] ?? null));

            // Si l'idée est validée, notifier la DPAF pour la rédaction de la note conceptuelle
            /*if ($decision === 'valider') {
                $dpafUsers = User::where('type', 'dpaf')->where('profilable_type', Dpaf::class)
                    ->where('status', 'actif')
                    ->whereHas('profilable', function ($query) use ($ideeProjet) {
                        $query->where('id_ministere', $ideeProjet->ministere->id);
                    })
                    ->get();

                if ($dpafUsers->count() > 0) {
                    // Récupérer le projet créé s'il existe
                    $projet = $ideeProjet->projet ?? null;
                    Notification::send($dpafUsers, new NotificationRedactionNoteConceptuelleNotification($ideeProjet, $projet));
                }
            }*/

            // Récupérer les décisions de validation
            $decisions = $ideeProjet->decisions()->with('observateur')->get();

            return response()->json([
                'success' => true,
                'message' => 'Idee de projet evaluer avec succès',
                'data' => $evaluation,
                'decisions' => $decisions
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $httpCode = $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la validation de l'idee de projet. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], $httpCode);
        }
    }

    /**
     * Récupérer les décisions de validation finale d'idée de projet vers projet
     */
    public function getDecisionsValidationIdeeDeProjetAProjet($ideeProjetId): JsonResponse
    {
        try {
            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer les évaluations de validation finale par analyste DGPD
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'validation-idee-projet-a-projet')
                ->whereNotNull('valider_par')
                ->whereNotNull('valider_le')
                ->where('statut', 1)
                ->with(['validator', 'commentaires'])
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'idee_projet' => new IdeesProjetResource($ideeProjet),
                    'evaluation' => $evaluation ? [
                        'id' => $evaluation->id,
                        'valider_le' => Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i"),
                        'valider_par' => new UserResource($evaluation->validator),
                        'decision' => $evaluation->evaluation,
                        'statut' => $evaluation->statut
                    ] : null
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la récupération des décisions finales. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }

    /**
     * Finalize evaluation and calculate final results.
     */
    public function finalizeEvaluation($ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'responsable-projet') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier que l'évaluation climatique existe
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            if ($evaluation->statut == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Score Auto Evaluation climatique déja enregistré',
                ], 400);
            }

            if (auth()->id() !== $ideeProjet->responsable->id) {
                throw new Exception("Vous n'avez pas les droits pour effectuer cette action", 403);
            }

            $completionPercentage = $this->calculateCompletionPercentage($evaluation);

            if ($completionPercentage != 100) {
                throw new Exception("Auto-evaluation climatique toujours en veuillez patientez", 403);
            }

            // Calculer les résultats finaux
            //$aggregatedScores = $evaluation->getAggregatedScores();

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
            $evaluateurs = $evaluation->evaluateursClimatique()->get();
            $criteres = $evaluation->criteres;
            $evaluationCriteres = new Collection();

            foreach ($evaluateurs as $evaluateur) {
                foreach ($criteres as $critere) {
                    $evaluationCritere = $evaluation->evaluationCriteres()
                        ->autoEvaluation()
                        ->active()
                        ->where('critere_id', $critere->id)
                        ->where('evaluateur_id', $evaluateur->id)
                        ->firstOrNew([
                            'evaluation_id' => $evaluation->id,
                            'critere_id' => $critere->id,
                            'evaluateur_id' => $evaluateur->id,
                        ], [
                            'categorie_critere_id' => $critere->categorie_critere_id,
                            'note' => 'En attente',
                            'notation_id' => null,
                            'is_auto_evaluation' => true,
                            'est_archiver' => false
                        ]);

                    $evaluationCriteres->push($evaluationCritere->load(['critere', 'notation', 'categorieCritere', 'evaluateur']));
                }
            }

            $aggregatedScores = $evaluation->aggregateScoresByCritere($evaluationCriteres);
            $finalResults = $this->calculateFinalResults($aggregatedScores);

            // Calculer et ajouter les scores
            $scoreGlobal = $this->calculateScoreGlobal($evaluation->id);
            $finalResults['score_global'] = $scoreGlobal;

            // Ajouter score climatique si c'est une évaluation climatique
            if ($evaluation->type_evaluation === 'climatique') {
                $scoreClimatique = $this->calculateScoreClimatique($evaluation->id);
                $finalResults['score_climatique'] = $scoreClimatique;
            }

            $grilleEvaluation = $this->categorieCritereRepository->getCanevasEvaluationClimatique();


            $ideeProjet->update([
                'score_climatique' => $finalResults['score_final_pondere'],
                'identifiant_bip' => $this->generateIdentifiantBip(),
                'statut' => StatutIdee::IDEE_DE_PROJET,  // Marquer comme terminée

                'phase' => $this->getPhaseFromStatut(StatutIdee::IDEE_DE_PROJET),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::IDEE_DE_PROJET),

                // Enregistrer le canevas climatique dans l'idée projet
                /**
                 * Enregistrement du canevas utiliser pour l'evaluation climatique dans l'idée projet
                 */
                'canevas_climatique' => $grilleEvaluation ? (new CategorieCritereResource($grilleEvaluation))->toArray(request()) : null,
            ]);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($ideeProjet, StatutIdee::IDEE_DE_PROJET);
            $this->enregistrerDecision($ideeProjet, 'Finalisation score climatique', 'Score climatique finalisé: ' . ($finalResults['score_final_pondere'] ?? 0));

            $evaluation->update([
                'resultats_evaluation' => $finalResults,
                'valider_le' => now(),
                'statut' => 1  // Marquer comme terminée
            ]);

            // Notifier le Responsable que l'évaluation climatique est terminée
            $responsable = $ideeProjet->responsable;
            if ($responsable) {
                Notification::send($responsable, new EvaluationClimatiqueFinaliseeNotification($ideeProjet, $evaluation));
            }

            // Notifier le Responsable hiérarchique qu'une nouvelle idée de projet a été créée
            $responsablesHierarchiques = User::where('type', 'responsable-hierachique')
                ->where('profilable_type', get_class($ideeProjet->ministere))
                ->where('profilable_id', $ideeProjet->ministere->id)
                ->get();

            if ($responsablesHierarchiques->count() > 0) {
                $scoreClimatique = $finalResults['score_final_pondere'] ?? 0;
                Notification::send($responsablesHierarchiques, new NouvelleIdeeProjetNotification($ideeProjet, $scoreClimatique));
            }

            $evaluation->refresh();

            return response()->json([
                'success' => true,
                'message' => "Score de l'auto-Évaluation climatique finalisée avec succès",
                'data' => $evaluation
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "'Erreur lors de l'enregistrement du score. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize evaluation and calculate final results.
     */
    public function refaireAutoEvaluationClimatique($ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'responsable-projet') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            if (auth()->id() !== $ideeProjet->responsable->id) {
                throw new Exception("Vous n'avez pas les droits pour effectuer cette action", 403);
            }

            // Vérifier que l'évaluation climatique existe
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            if ($evaluation->statut == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Auto Evaluation climatique déja validé',
                ], 400);
            }

            $completionPercentage = $this->calculateCompletionPercentage($evaluation);

            if ($completionPercentage != 100) {
                throw new Exception("Auto-evauation climatique toujours en veuillez patientez", 403);
            }

            $criteresEvaluation = $evaluation->evaluationCriteres()
                ->autoEvaluation()
                ->active()->get();

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
            $evaluateurs = $evaluation->evaluateursClimatique()->get();


            if ($evaluateurs->count() == 0) {
                throw new Exception('Aucun évaluateur trouvé avec la permission "effectuer-evaluation-climatique-idee-projet"', 404);
            }

            $ideeProjet->update([
                'est_soumise' => false,
                //'score_climatique' => 0,
                //'identifiant_bip' => null, //$this->generateIdentifiantBip(),
                'statut' => StatutIdee::BROUILLON,  // Marquer comme terminée
                'phase' => $this->getPhaseFromStatut(StatutIdee::BROUILLON),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::BROUILLON),
            ]);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($ideeProjet, StatutIdee::BROUILLON);
            $this->enregistrerDecision($ideeProjet, 'Réévaluation climatique demandée', 'Score climatique insatisfaisant - Retour en phase de rédaction');

            $evaluation->update([
                'resultats_evaluation' => [],
                'evaluation' => [],
                'valider_le' => null,
                'statut' => 0  // Marquer comme terminée
            ]);

            $criteresEvaluation->each->update(["est_archiver" => true]);

            DB::commit();

            // Notifier les évaluateurs de la décision sur le faible score climatique
            $scoreClimatique = $ideeProjet->score_climatique ?? 0;
            Notification::send($evaluateurs, new DecisionFaibleScoreClimatiqueNotification($ideeProjet, $scoreClimatique, 'reevaluer', 'Score climatique insatisfaisant - Réévaluation demandée'));

            // Notifier les évaluateurs assignés pour la nouvelle évaluation
            //Notification::send($evaluateurs, new EvaluationClimatiqueAssigneeNotification($ideeProjet, $evaluation));

            return response()->json([
                'success' => true,
                'message' => 'Score climatique insatisfaisant, Invitation climatique renvoyer',
                'data' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la relance de l'evaluation climatique. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate completion percentage of evaluation.
     */
    private function calculateCompletionPercentage(Evaluation $evaluation, $type = "climatique"): float
    {
        /*$evaluateurs =($type == "pertinence" ? $evaluation->evaluateursPertinence() : $evaluation->evaluateursClimatique());
        $totalEvaluateurs = ($type == "pertinence" ? $evaluation->evaluateursPertinence() : $evaluation->evaluateursClimatique())->count();
        $totalEvaluateurs = $evaluateurs->get()->count(); // ✅ on vérifie bien si la collection n'est pas vide;
        */


        if ($evaluation->statut != 1) {

            $evaluateurs = ($type == "pertinence" ? $evaluation->evaluateursPertinence() : $evaluation->evaluateursClimatique());
            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
            $totalEvaluateurs = $evaluateurs->get()->count();
            /*
                $evaluation->evaluateursClimatique()
                            ->get()->count(); // ✅ on vérifie bien si la collection n'est pas vide;
            */

        } else {
            $totalEvaluateurs = $evaluation->evaluateursDeEvalPreliminaireClimatique()
                ->select('users.*')
                ->distinct('users.id')
                ->count();
        }

        $totalCriteres = $evaluation->criteres->count();

        $completedCriteres = $evaluation->evaluationCriteres()
            ->autoEvaluation()
            ->active()
            ->whereNotNull('notation_id')
            ->where('note', '!=', 'En attente')
            ->count();
        $totalEvaluationsAttendues = $totalEvaluateurs * $totalCriteres;
        return $totalCriteres > 0 ? ($completedCriteres * 100 / $totalEvaluationsAttendues) : 0;
    }

    /**
     * Calculate final results from aggregated scores with ponderation.
     */
    private function calculateFinalResults(object $aggregatedScores, $outil = "climatique"): array
    {
        $results = [];
        $total_score_pondere = 0;
        $total_ponderation = 0;

        foreach ($aggregatedScores as $critereId => $data) {
            $score_pondere = $data['score_pondere'] ?? 0;
            $ponderation = $data['ponderation'] ?? 0;

            $results[] = [
                'critere_id' => $critereId,
                'critere_nom' => $data['critere']->intitule ?? 'N/A',
                'ponderation' => $ponderation,
                'ponderation_pct' => $ponderation . '%',
                'moyenne_evaluateurs' => $data['moyenne_evaluateurs'] ?? 0,
                'score_pondere' => $score_pondere
            ];

            // Ajouter la clé uniquement si elle existe
            if (isset($data['total_evaluateurs'])) {
                $result['total_evaluateurs'] = $data['total_evaluateurs'];
            }

            // Ajouter la clé uniquement si elle existe
            if (isset($data['evaluateurs'])) {
                $result['evaluateurs'] = $data['evaluateurs'];
            }

            $total_score_pondere += $score_pondere;
            $total_ponderation += $ponderation;
        }

        // Calcul du score final pondéré global
        $score_final_pondere = $total_ponderation > 0 ?
            (($total_score_pondere / $total_ponderation) * 100) : 0;

        $critereCount = CategorieCritere::where("slug", $outil == "climatique" ? "evaluation-preliminaire-multi-projet-impact-climatique" : ("pertinence" ? "grille-evaluation-pertinence-idee-projet" : 'grille-analyse-multi-critere'))->first()->criteres->count(); //count($results);

        return [
            'scores_ponderes_par_critere' => $results,
            'score_final_pondere' => $critereCount ? $score_final_pondere / $critereCount : 0,
            'total_ponderation' => $total_ponderation,
            'nombre_criteres' => $critereCount
        ];
    }

    /**
     * Calculate variance of notes.
     */
    private function calculateVariance(array $notes): float
    {
        if (count($notes) <= 1) {
            return 0;
        }

        $mean = array_sum($notes) / count($notes);
        $variance = array_sum(array_map(function ($note) use ($mean) {
            return pow($note - $mean, 2);
        }, $notes)) / count($notes);

        return $variance;
    }

    /**
     * Soumettre les réponses d'évaluation climatique pour un évaluateur.
     */
    public function soumettreEvaluationClimatique(array $data, $ideeProjetId): JsonResponse
    {
        try {

            DB::beginTransaction();

            $evaluateurId = auth()->id();
            $reponses = $data['reponses'];

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier si l'idée de projet est soumise - si non, refuser l'évaluation
            if ($ideeProjet->est_soumise !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'évaluation climatique ne peut être effectuée que sur des idées de projet soumises.',
                ], 403);
            }

            $evaluation = Evaluation::where(
                'projetable_id',
                $ideeProjet->id
            )->where(
                'projetable_type',
                get_class($ideeProjet)
            )
                ->where('type_evaluation', 'climatique')
                ->first();

            if ($ideeProjet->statut != StatutIdee::BROUILLON && ($evaluation?->statut == 1 && $evaluation?->date_fin_evaluation != null)) {
                throw new Exception("Evaluation climatique deja termine", 403);
            }

            $is_auto_evaluation = auth()->user()->type == "analyste-dgpd" ? false : true;

            $evaluation = Evaluation::updateOrCreate([
                'projetable_id' => $ideeProjet->id,
                'projetable_type' => get_class($ideeProjet),
                "type_evaluation" => "climatique"
            ], [
                "type_evaluation" => "climatique",
                "statut"  => $is_auto_evaluation ? 0 : 1,
                "date_fin_evaluation" => $is_auto_evaluation ? null : now()
            ]);

            $isAssigned = false;
            if ($is_auto_evaluation == false && auth()->user()->type == "analyste-dgpd" && auth()->user()->profilable_type == Dgpd::class) {
                $isAssigned = true;
            } elseif ($is_auto_evaluation) {
                if ((auth()->user()->profilable_type == Organisation::class || auth()->user()->profilable_type == Dpaf::class) && auth()->user()->profilable?->ministere && $ideeProjet->ministere && (auth()->user()->profilable->ministere?->id == $ideeProjet->ministere->id)) {
                    $isAssigned = $evaluation->evaluateursClimatique()->where("id", auth()->id())->first()?->hasPermissionTo('effectuer-evaluation-climatique-idee-projet');
                }
            }

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas assigné comme évaluateur pour cette évaluation climatique.',
                ], 403);
            }

            // Vérifier et mettre à jour les réponses
            foreach ($reponses as $reponse) {
                // Vérifier que le critère appartient à la bonne catégorie ou est obligatoire
                $critere = Critere::with('categorie_critere')->find($reponse['critere_id']);

                if (!$critere) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Critère non trouvé avec l\'ID: ' . $reponse['critere_id'],
                    ], 400);
                }

                // Vérifier les conditions d'éligibilité du critère
                $isEligible = false;

                // Cas 1: Le critère appartient à la catégorie spécifique
                if (
                    $critere->categorie_critere &&
                    $critere->categorie_critere->slug === 'evaluation-preliminaire-multi-projet-impact-climatique'
                ) {
                    $isEligible = true;
                }

                // Cas 2: Le critère n'a pas de catégorie mais est obligatoire
                if (is_null($critere->categorie_critere_id) && $critere->is_mandatory) {
                    $isEligible = true;
                }

                if (!$isEligible) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le critère "' . $critere->intitule . '" n\'est pas éligible pour cette évaluation climatique.',
                    ], 400);
                }

                $notation = Notation::find($reponse['notation_id']);

                if (!$notation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Notation non trouvé avec l\'ID: ' . $reponse['notation_id'],
                    ], 400);
                }

                $evaluationCritere = EvaluationCritere::where([
                    'evaluation_id' => $evaluation->id,
                    'evaluateur_id' => $evaluateurId,
                    'categorie_critere_id' => $reponse["categorie_critere_id"],
                    'critere_id' => $reponse['critere_id'],
                    'est_archiver' => false, // ← ici la condition
                    'is_auto_evaluation' => $is_auto_evaluation,
                ])->first();


                if ($evaluationCritere) {
                    $evaluationCritere->fill([
                        'notation_id' => $notation->id,
                        'note' => $notation->valeur,
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'is_auto_evaluation' => $is_auto_evaluation,
                        'updated_at' => now(),
                    ]);
                    $evaluationCritere->save();
                } else {
                    $evaluationCritere = EvaluationCritere::create([
                        'evaluation_id' => $evaluation->id,
                        'evaluateur_id' => $evaluateurId,
                        'categorie_critere_id' => $reponse["categorie_critere_id"],
                        'critere_id' => $reponse['critere_id'],
                        'notation_id' => $notation->id,
                        'note' => $notation->valeur,
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'is_auto_evaluation' => $is_auto_evaluation,
                        'est_archiver' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $evaluation->refresh();

            if ($evaluation->statut == -1) {

                $evaluation->statut = 0;

                $evaluation->save();
            }

            $evaluationClimatiqueReponses = new Collection([]);

            if (auth()->user()->type == "analyste-dgpd") {

                $outilAMC = CategorieCritere::where("slug", 'grille-analyse-multi-critere')->first();

                if (!$outilAMC) {
                    throw new Exception("Outil AMC introuvable", 404);
                }

                $critereImpactClimatique = $outilAMC->criteres()->whereRaw('LOWER(intitule) LIKE ?', [/* '%impact climatique%', '%climatique%',  */'%climat%'])/* ->where("intitule", "Impact climatique") */->first();

                if (!$critereImpactClimatique) {
                    throw new Exception("Critere 'Impact Climatique' de l'AMC introuvrable", 403);
                }

                // Récupérer les réponses de l'évaluateur connecté
                $evaluationClimatiqueReponses = EvaluationCritere::forEvaluation($evaluation->id)
                    ->evaluationExterne()
                    ->active()
                    ->with(['critere', 'notation', 'categorieCritere'])
                    ->get();

                $score_pondere_par_critere = $evaluationClimatiqueReponses->groupBy('critere_id')->map(function ($critereEvaluations) {
                    $critere = $critereEvaluations->first()->critere;

                    $notes = $critereEvaluations->pluck('notation.valeur')->filter();

                    $moyenne_evaluateurs = $notes->average();
                    return [
                        'critere' => $critere,
                        'ponderation' => $critere->ponderation,
                        'ponderation_pct' => $critere->ponderation . '%',
                        'score_pondere' => $moyenne_evaluateurs * ($critere->ponderation / 100)
                    ];
                });

                $score_climatique = $evaluation->criteres->count() ? ($score_pondere_par_critere->sum('score_pondere') / $evaluation->criteres->count()) : 0;

                $evaluationAMC = Evaluation::updateOrCreate([
                    'projetable_id' => $ideeProjet->id,
                    'projetable_type' => get_class($ideeProjet),
                    "type_evaluation" => "amc"
                ], [
                    "type_evaluation" => "amc",
                    "statut"  => 0,
                    "date_debut_evaluation" => now(),
                    "evaluation" => [
                        "climatique" => [
                            "score_climatique" => $score_climatique,
                            "scores_pondere_par_critere" => array_values($score_pondere_par_critere->toArray()),
                            "evaluation_effectuer" => EvaluationCritereResource::collection($evaluationClimatiqueReponses)
                        ]
                    ],
                    "resultats_evaluation" => []
                ]);

                $evaluationCritere = EvaluationCritere::updateOrCreate(
                    [
                        'evaluation_id' => $evaluationAMC->id,
                        'critere_id' => $critereImpactClimatique->id,
                        'categorie_critere_id' => $outilAMC->id,
                    ],
                    [
                        'evaluation_id' => $evaluationAMC->id,
                        'notation_id' => null,
                        'evaluateur_id' => $evaluateurId,
                        'note' => $score_climatique,
                        'commentaire' => "",
                        'is_auto_evaluation' => false,
                        'est_archiver' => false,
                        'updated_at' => now(),
                    ]
                );

                $ideeProjet->update([
                    'statut' => StatutIdee::AMC,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::AMC),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::AMC),
                ]);
            } else {
                // Récupérer les réponses de l'évaluateur connecté
                $evaluationClimatiqueReponses = EvaluationCritere::forEvaluation($evaluation->id)
                    ->autoEvaluation()
                    ->active()
                    ->byEvaluateur($evaluateurId)
                    ->with(['critere', 'notation', 'categorieCritere'])
                    ->get();

                if ($evaluation->statut == 0) {
                    $evaluation->update([
                        "evaluation" => EvaluationCritereResource::collection($evaluationClimatiqueReponses),
                        //"resultats_evaluation" => []
                    ]);
                }
            }

            DB::commit();

            // Calculer la progression et notifier le Responsable projet
            $tauxProgression = $this->calculateCompletionPercentage($evaluation);
            $responsableProjet = $ideeProjet->responsable;

            if ($responsableProjet && $is_auto_evaluation) {
                $scoreClimatique = $ideeProjet->score_climatique;
                Notification::send($responsableProjet, new ProgressionEvaluationClimatiqueNotification($ideeProjet, $evaluation, $tauxProgression, $scoreClimatique));

                // Si c'est le dernier évaluateur qui termine (progression = 100%)
                if ($tauxProgression >= 100) {
                    $scoreClimatiqueFinal = $this->calculateScoreClimatique($evaluation->id)['score_climatique'] ?? 0;
                    Notification::send($responsableProjet, new EvaluationClimatiqueTermineNotification($ideeProjet, $evaluation, $scoreClimatiqueFinal));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Réponses d\'évaluation climatique soumises avec succès',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'reponses_soumises' => count($reponses),
                    'evaluateur_reponses' => EvaluationCritereResource::collection($evaluationClimatiqueReponses),
                    'evaluateur_stats' => [
                        'total_criteres' => $evaluationClimatiqueReponses->count(),
                        'criteres_evalues' => $evaluationClimatiqueReponses->filter->isCompleted()->count(),
                        'criteres_en_attente' => $evaluationClimatiqueReponses->filter->isPending()->count(),
                        'taux_completion' => $evaluationClimatiqueReponses->count() > 0 ?
                            (($evaluationClimatiqueReponses->filter->isCompleted()->count() / $evaluationClimatiqueReponses->count()) * 100) : 0
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Évaluation climatique non trouvée pour cette idée de projet',
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e ? $e->getMessage() : 'Erreur lors de la soumission des réponses',
                'error' => $e->getMessage()
            ], $e ? ($e->getCode() ?? 500) : 500);
        }
    }

    /**
     * Calculer le score pondéré d'un évaluateur spécifique.
     */
    private function calculateEvaluateurScorePondere($evaluationCriteres): array
    {
        $criteres_avec_score = $evaluationCriteres->filter->isCompleted();

        if ($criteres_avec_score->isEmpty()) {
            return [
                'score_total' => 0,
                'ponderation_totale' => 0,
                'score_final' => 0,
                'nombre_criteres_evalues' => 0
            ];
        }

        $score_total = 0;
        $ponderation_totale = 0;

        foreach ($criteres_avec_score as $evaluationCritere) {
            $note = $evaluationCritere->getNumericValue() ?? 0;
            $ponderation = $evaluationCritere->critere->ponderation ?? 0;

            $score_total += $note * ($ponderation / 100);
            $ponderation_totale += $ponderation;
        }

        $score_final = $ponderation_totale > 0 ?
            (($score_total * 100) / $ponderation_totale) : 0;

        return [
            'score_total' => $score_total,
            'ponderation_totale' => $ponderation_totale,
            'score_final' => $score_final,
            'nombre_criteres_evalues' => $criteres_avec_score->count()
        ];
    }

    /**
     * Dashboard pour responsable : informations complètes de l'évaluation climatique.
     */
    public function getDashboardEvaluationClimatique($ideeProjetId): JsonResponse
    {
        try {

            dd($ideeProjetId);

            if (auth()->user()->profilable_type == Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acceder a cette resource", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $evaluation = Evaluation::firstOrCreate([
                'projetable_id' => $ideeProjet->id,
                'projetable_type' => get_class($ideeProjet),
                "type_evaluation" => "climatique"
            ], [
                "type_evaluation" => "climatique",
                "statut"  => 0,
                "date_debut_evaluation" => now(),
                "evaluation" => [],
                "resultats_evaluation" => []
            ]);

            // Vérifier que c'est une évaluation climatique
            if ($evaluation->type_evaluation !== 'climatique') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette évaluation n\'est pas de type climatique'
                ], 400);
            }

            if ($evaluation->statut != 1) {
                // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
                $evaluateurs = $evaluation->evaluateursClimatique()->get();
            } else {

                $evaluateurs = $evaluation->evaluateursDeEvalPreliminaireClimatique()
                    ->select('users.*')
                    ->distinct('users.id')
                    ->get();
            }

            /*  User::when($ideeProjet->ministere, function ($query) use ($ideeProjet) {
                        $query->where(function ($q) use ($ideeProjet) {
                            $q->where('profilable_type', get_class($ideeProjet->ministere))
                                ->where('profilable_id', $ideeProjet->ministere->id);
                        });
                    })
                    ->when($ideeProjet->responsable?->profilable->ministere, function ($query) use ($ideeProjet) {
                        $ministere = $ideeProjet->responsable->profilable->ministere;
                        $query->orWhere(function ($q) use ($ministere) {
                            $q->where('profilable_type', get_class($ministere))
                                ->where('profilable_id', $ministere->id);
                        });
                    })
            */

            if ($evaluateurs->count() == 0) {
                throw new Exception('Aucun évaluateur trouvé avec la permission "effectuer-evaluation-climatique-idee-projet"', 404);
            }

            // Récupérer les critères éligibles pour l'évaluation climatique
            $criteres = $evaluation->criteres;

            $evaluationCriteres = new Collection();

            // Assigner chaque évaluateur à tous les critères
            foreach ($evaluateurs as $evaluateur) {
                foreach ($criteres as $critere) {
                    $evaluationCritere = $evaluation->evaluationCriteres()
                        ->autoEvaluation()
                        ->active()
                        ->where('critere_id', $critere->id)
                        ->where('evaluateur_id', $evaluateur->id)
                        ->firstOrNew([
                            'evaluation_id' => $evaluation->id,
                            'critere_id' => $critere->id,
                            'evaluateur_id' => $evaluateur->id,
                        ], [
                            'categorie_critere_id' => $critere->categorie_critere_id,
                            'note' => 'En attente',
                            'notation_id' => null,
                            'is_auto_evaluation' => true,
                            'est_archiver' => false
                        ]);

                    $evaluationCriteres->push($evaluationCritere->load(['critere', 'notation', 'categorieCritere', 'evaluateur']));
                }
            }

            $aggregatedScores = $evaluation->aggregateScoresByCritere($evaluationCriteres);

            // Récupérer tous les critères avec leurs évaluations
            /*$evaluationCriteres = EvaluationCritere::forEvaluation($evaluation->id)
                ->autoEvaluation()
                ->active()
                ->with(['critere', 'notation', 'categorieCritere', 'evaluateur'])
                ->get();

            // Calculs globaux
            $aggregatedScores = $evaluation->getAggregatedScores();
            dd($evaluationCriteres);
            */
            $finalResults = $this->calculateFinalResults($aggregatedScores);
            $completionPercentage = $this->calculateCompletionPercentage($evaluation);

            // Progression par évaluateur
            $progressionParEvaluateur = $this->calculateProgressionParEvaluateur($evaluationCriteres);

            // Statistiques générales
            $totalEvaluateurs = $evaluationCriteres->pluck('evaluateur_id')->unique()->count();
            $totalCriteres = $evaluation->criteres->count();
            $totalEvaluationsCompletes = $evaluationCriteres->filter->isCompleted()->count();
            $totalEvaluationsAttendues = $totalEvaluateurs * $totalCriteres;

            return response()->json([
                'success' => true,
                'data' => [
                    "statut_idee" => $ideeProjet->statut,
                    "idee_projet" => new IdeesProjetResource($ideeProjet),
                    'evaluation' => new EvaluationResource($evaluation),

                    //'score_climatique' => $scoreClimatique,

                    // Taux de progression global
                    'taux_progression_global' => [
                        'pourcentage' => $completionPercentage,
                        'evaluations_completes' => $totalEvaluationsCompletes,
                        'evaluations_attendues' => $totalEvaluationsAttendues,
                        'evaluateurs_total' => $totalEvaluateurs,
                        'criteres_total' => $totalCriteres
                    ],

                    // Progression par évaluateur
                    'progression_par_evaluateur' => $progressionParEvaluateur,

                    // Résultats agrégés et finaux
                    //'aggregated_scores' => $aggregatedScores,
                    'final_results' => $finalResults
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?? 'Erreur lors de la récupération du dashboard',
                'error' => $e->getMessage()
            ], $e ? ($e->getCode() ?? 500) : 500);
        }
    }

    /**
     * Calculer la progression par évaluateur.
     */
    private function calculateProgressionParEvaluateur($evaluationCriteres): array
    {
        return $evaluationCriteres->groupBy('evaluateur_id')->map(function ($criteres, $evaluateurId) {
            $evaluateur = $criteres->first()->evaluateur;
            $criteres_completes = $criteres->filter->isCompleted();
            $total_criteres = $criteres->count();

            return [
                'evaluateur' => [
                    'id' => $evaluateur->id ?? $evaluateurId,
                    'nom_complet' => $evaluateur->personne->nom . ' ' . $evaluateur->personne->prenom ?? 'Inconnu',
                    'email' => $evaluateur->email ?? null
                ],
                'criteres_evalues' => $criteres_completes->count(),
                'total_criteres' => $total_criteres,
                'taux_completion' => $total_criteres > 0 ?
                    (($criteres_completes->count() / $total_criteres) * 100) : 0,
                'score_pondere_individuel' => $this->calculateEvaluateurScorePondere($criteres),

                'evaluateur_reponses' => EvaluationCritereResource::collection($criteres),

                'derniere_evaluation' => Carbon::parse($criteres->max('updated_at'))->format('d/m/Y H:m:i'),
                'statut' => $criteres_completes->count() === $total_criteres ? 'Terminé' : ($criteres_completes->count() > 0 ? 'En cours' : 'Non commencé')
            ];
        })->values()->toArray();
    }

    /**
     * Calculer les scores pondérés par critère.
     */
    private function calculateScoresPondereParCritere($aggregatedScores): array
    {
        return $aggregatedScores->map(function ($data, $critereId) {
            $critere = $data['critere'];
            $moyenneEvaluateurs = $data['moyenne_evaluateurs'] ?? 0;
            $scorePondere = $data['score_pondere'] ?? 0;

            return [
                'critere_id' => $critereId,
                'critere_nom' => $critere->intitule ?? 'N/A',
                'ponderation' => $data['ponderation'] ?? 0,
                'ponderation_pct' => ($data['ponderation'] ?? 0) . '%',
                'moyenne_evaluateurs' => $moyenneEvaluateurs,
                'score_pondere' => $scorePondere,
                'total_evaluateurs' => $data['total_evaluateurs'] ?? 0,
                'notes_individuelles' => $data['notes_individuelles'] ?? [],
                'evaluateurs_liste' => $data['evaluateurs'] ?? []
            ];
        })->values()->toArray();
    }

    /**
     * Changer le statut d'une évaluation.
     */
    public function changeEvaluationStatus(int $evaluationId, int $statut): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);

            $evaluation->update(['statut' => $statut]);

            $statusText = $evaluation->getStatutTextAttribute();

            return response()->json([
                'success' => true,
                'message' => "Statut de l'évaluation changé vers: {$statusText}",
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'statut' => $evaluation->statut,
                    'statut_text' => $statusText
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archiver des critères d'évaluation.
     */
    public function archiveEvaluationCriteres(array $critereIds): JsonResponse
    {
        try {
            DB::beginTransaction();

            $archived = EvaluationCritere::whereIn('id', $critereIds)
                ->update(['est_archiver' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$archived} critères d'évaluation archivés avec succès",
                'data' => ['archived_count' => $archived]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'archivage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurer des critères d'évaluation archivés.
     */
    public function unarchiveEvaluationCriteres(array $critereIds): JsonResponse
    {
        try {
            DB::beginTransaction();

            $unarchived = EvaluationCritere::whereIn('id', $critereIds)
                ->update(['est_archiver' => false]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$unarchived} critères d'évaluation restaurés avec succès",
                'data' => ['unarchived_count' => $unarchived]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les critères d'évaluation automatiques.
     */
    public function getAutoEvaluationCriteres(int $evaluationId): JsonResponse
    {
        try {
            $criteres = EvaluationCritere::forEvaluation($evaluationId)
                ->autoEvaluation()
                ->active()
                ->with(['critere', 'notation', 'categorieCritere', 'evaluateur'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation_id' => $evaluationId,
                    'count' => $criteres->count(),
                    'auto_criteres' => EvaluationCritereResource::collection($criteres)
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des critères automatiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer le score climatique d'une évaluation.
     * Le score climatique est la moyenne pondérée de tous les critères évalués.
     */
    public function calculateScoreClimatique(int $evaluationId): array
    {
        $evaluation = Evaluation::where('id', $evaluationId)
            ->where('type_evaluation', 'climatique')
            ->firstOrFail();

        // Récupérer tous les critères complétés pour cette évaluation
        $criteres = EvaluationCritere::forEvaluation($evaluation->id)
            ->autoEvaluation()
            ->active()
            ->completed()
            ->with(['critere', 'notation'])
            ->get();

        if ($criteres->isEmpty()) {
            return [
                'score_climatique' => 0,
                'score_pourcentage' => 0,
                'nombre_criteres_evalues' => 0,
                'ponderation_totale' => 0,
                'criteres_details' => [],
                'statut_evaluation' => 'Aucun critère évalué'
            ];
        }

        // Grouper par critère et calculer la moyenne pour chaque critère
        $criteresMoyennes = $criteres->groupBy('critere_id')->map(function ($critereEvaluations) {
            $critere = $critereEvaluations->first()->critere;
            $notes = $critereEvaluations->pluck('notation.valeur')->filter()->map(function ($note) {
                return is_numeric($note) ? (float) $note : 0;
            });

            $moyenne = $notes->average();
            $ponderation = $critere->ponderation ?? 0;

            return [
                'critere_id' => $critere->id,
                'critere_nom' => $critere->intitule,
                'moyenne_critere' => $moyenne,
                'ponderation' => $ponderation,
                'score_pondere' => $moyenne * ($ponderation / 100),
                'nombre_evaluateurs' => $critereEvaluations->count(),
                'notes_individuelles' => $notes->toArray()
            ];
        });

        // Calculer le score climatique global
        $scoreTotal = $criteresMoyennes->sum('score_pondere');
        $ponderationTotale = $criteresMoyennes->sum('ponderation');

        // Score climatique sur l'échelle utilisée (généralement sur 5)
        $scoreClimatique = $ponderationTotale > 0 ?
            (($scoreTotal * 100) / $ponderationTotale) : 0;

        return [
            //'score_climatique' => $scoreClimatique,
            "score_climatique" => ($criteresMoyennes->avg('score_pondere')),
            'nombre_criteres_evalues' => $criteresMoyennes->count(),
            'ponderation_totale' => $ponderationTotale,
            'criteres_details' => $criteresMoyennes->values()->toArray(),
            'statut_evaluation' => $this->getStatutScoreClimatique($scoreClimatique)
        ];
    }

    /**
     * Obtenir le score climatique d'une évaluation avec détails.
     */
    public function getScoreClimatique(int $evaluationId): JsonResponse
    {
        try {
            $evaluation = Evaluation::where('id', $evaluationId)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            $scoreData = $this->calculateScoreClimatique($evaluationId);

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation_id' => $evaluationId,
                    'type_evaluation' => $evaluation->type_evaluation,
                    'score_climatique_details' => $scoreData,
                    'evaluation_info' => [
                        'statut' => $evaluation->statut,
                        'statut_text' => $evaluation->getStatutTextAttribute(),
                        'date_debut' => $evaluation->date_debut_evaluation,
                        'date_fin' => $evaluation->date_fin_evaluation,
                        'valide_le' => $evaluation->valider_le
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul du score climatique',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le statut qualitatif basé sur le score climatique.
     */
    private function getStatutScoreClimatique(float $score): string
    {
        return match (true) {
            $score >= 0.67 => 'Impact climatique faible',
            default => 'Impact climatique forte'
        };
    }

    /**
     * Calculer le score global de tous les critères d'une évaluation.
     * Applicable à tous les types d'évaluations (climatique, technique, etc.)
     */
    public function calculateScoreGlobal(int $evaluationId): array
    {
        $evaluation = Evaluation::findOrFail($evaluationId);

        // Récupérer tous les critères complétés pour cette évaluation
        $criteres = EvaluationCritere::forEvaluation($evaluationId)
            ->autoEvaluation()
            ->active()
            ->completed()
            ->with(['critere', 'notation', 'categorieCritere'])
            ->get();

        if ($criteres->isEmpty()) {
            return [
                'score_global' => 0,
                'score_pourcentage' => 0,
                'nombre_criteres_evalues' => 0,
                'ponderation_totale' => 0,
                'criteres_details' => [],
                'scores_par_categorie' => [],
                'statut_evaluation' => 'Aucun critère évalué',
                'niveau_performance' => 'Non évalué'
            ];
        }

        // Grouper par critère et calculer la moyenne pour chaque critère
        $criteresMoyennes = $criteres->groupBy('critere_id')->map(function ($critereEvaluations) {
            $critere = $critereEvaluations->first()->critere;
            $categorie = $critereEvaluations->first()->categorieCritere;
            $notes = $critereEvaluations->pluck('notation.valeur')->filter()->map(function ($note) {
                return is_numeric($note) ? (float) $note : 0;
            });

            $moyenne = $notes->count() > 0 ? $notes->average() : 0;
            $ponderation = $critere->ponderation ?? 0;

            // Calculer variance et écart-type seulement s'il y a des notes
            $variance = 0;
            $ecartType = 0;
            if ($notes->count() > 0) {
                $variance = $this->calculateVariance($notes->toArray());
                $ecartType = (sqrt($variance));
            }

            return [
                'critere_id' => $critere->id,
                'critere_nom' => $critere->intitule,
                'categorie_id' => $categorie->id ?? null,
                'categorie_nom' => $categorie->type ?? 'Aucune catégorie',
                'moyenne_critere' => $moyenne,
                'ponderation' => $ponderation,
                'score_pondere' => $moyenne * ($ponderation / 100),
                'nombre_evaluateurs' => $critereEvaluations->count(),
                'notes_individuelles' => $notes->toArray(),
                'variance' => ($variance),
                'ecart_type' => $ecartType
            ];
        });

        // Calculer les scores par catégorie
        $scoresParCategorie = $criteresMoyennes->groupBy('categorie_id')->map(function ($criteres, $categorieId) {
            $categorieName = $criteres->first()['categorie_nom'];
            $scoreTotal = $criteres->sum('score_pondere');
            $ponderationTotale = $criteres->sum('ponderation');
            $scoreMoyen = $ponderationTotale > 0 ? ($scoreTotal * 100) / $ponderationTotale : 0;

            return [
                'categorie_id' => $categorieId,
                'categorie_nom' => $categorieName,
                'nombre_criteres' => $criteres->count(),
                'score_moyen_categorie' => $scoreMoyen,
                'ponderation_totale' => $ponderationTotale,
                'score_pondere_categorie' => $scoreTotal
            ];
        });

        // Calculer le score global
        $scoreTotal = $criteresMoyennes->sum('score_pondere');
        $ponderationTotale = $criteresMoyennes->sum('ponderation');

        // Score global sur l'échelle utilisée (généralement sur 5)
        $scoreGlobal = $ponderationTotale > 0 ?
            (($scoreTotal * 100) / $ponderationTotale) : 0;

        // Pourcentage
        $scorePourcentage = (($scoreGlobal / 5) * 100);

        return [
            'score_global' => $scoreGlobal,
            'score_pourcentage' => $scorePourcentage,
            'nombre_criteres_evalues' => $criteresMoyennes->count(),
            'ponderation_totale' => $ponderationTotale,
            'criteres_details' => $criteresMoyennes->values()->toArray(),
            'scores_par_categorie' => $scoresParCategorie->values()->toArray()
        ];
    }

    /**
     * Obtenir le score global d'une évaluation avec détails.
     */
    public function getScoreGlobal(int $evaluationId): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);
            $scoreData = $this->calculateScoreGlobal($evaluationId);

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation_id' => $evaluationId,
                    'type_evaluation' => $evaluation->type_evaluation,
                    'score_global_details' => $scoreData,
                    'evaluation_info' => [
                        'statut' => $evaluation->statut,
                        'statut_text' => $evaluation->getStatutTextAttribute(),
                        'date_debut' => $evaluation->date_debut_evaluation,
                        'date_fin' => $evaluation->date_fin_evaluation,
                        'valide_le' => $evaluation->valider_le
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul du score global',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //calculateScoreGlobal
    /**
     * Mettre à jour le score climatique dans les résultats de l'évaluation.
     */
    public function updateScoreClimatiqueInResults(int $evaluationId): bool
    {
        try {
            $evaluation = Evaluation::where('id', $evaluationId)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();
            $scoreData = $this->calculateScoreClimatique($evaluationId);

            // Récupérer les résultats existants ou créer un nouveau tableau
            $resultatsExistants = $evaluation->resultats_evaluation ?? [];

            // Ajouter ou mettre à jour le score climatique
            $resultatsExistants['score_climatique'] = $scoreData;

            $evaluation->update(['resultats_evaluation' => $resultatsExistants]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Soumettre les réponses d'évaluation climatique pour un évaluateur.
     */
    public function appliquerAMC(array $data, $ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'analyste-dgpd' && auth()->user()->type !== 'dgpd' && !auth()->user()->hasPermissionTo('effectuer-l-amc-d-une-idee-de-projet')) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            $evaluateurId = auth()->id();
            $reponses = $data['reponses'];

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (($ideeProjet->statut != StatutIdee::AMC && $ideeProjet->statut != StatutIdee::ANALYSE)) {
                throw new Exception("AMC deja effectuer", 403);
            }

            $evaluationClimatique = Evaluation::where(
                'projetable_id',
                $ideeProjet->id
            )->where(
                'projetable_type',
                get_class($ideeProjet)
            )->where('type_evaluation', 'climatique')
                ->first();

            if (!$evaluationClimatique) throw new Exception("L'auto-Evaluation climatique pas encore effectuer", 403);

            if ($evaluationClimatique->statut != 1) {
                throw new Exception("l'Auto-Evaluation climatique pas encore effectuer ou terminer", 403);
            }

            if ($evaluationClimatique->statut == 1 && $evaluationClimatique->date_fin_evaluation == null) {
                throw new Exception("Veuillez effectuez l'evaluation climatique d'abord", 403);
            }

            $evaluation = Evaluation::where(
                'projetable_id',
                $ideeProjet->id
            )->where(
                'projetable_type',
                get_class($ideeProjet)
            )->where('type_evaluation', "amc")->first();

            if ($evaluation->statut == 1) {
                throw new Exception("Evaluation de l'amc deja effectuer", 403);
            }

            // Vérifier que l'évaluation climatique existe
            /* $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail(); */

            $evaluation = Evaluation::updateOrCreate([
                'projetable_id' => $ideeProjet->id,
                'projetable_type' => get_class($ideeProjet),
                "type_evaluation" => "amc"
            ], [
                "statut"  => 0,
                "date_debut_evaluation" => now(),
                "evaluation" => [...($evaluation->evaluation ?? [])],
                "resultats_evaluation" => [...($evaluation->evaluation ??  [])]
            ]);

            // Vérifier et mettre à jour les réponses
            foreach ($reponses as $reponse) {
                $isEvalClimatique = false;
                // Vérifier que le critère appartient à la bonne catégorie ou est obligatoire
                $critere = Critere::with('categorie_critere')->find($reponse['critere_id']);

                if (!$critere) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Critère non trouvé avec l\'ID: ' . $reponse['critere_id'],
                    ], 400);
                }
                if (str_contains(strtolower($critere->intitule ?? ''), 'impact climatique')) {
                    $isEvalClimatique = true;
                }

                // Vérifier les conditions d'éligibilité du critère
                $isEligible = false;

                // Cas 1: Le critère appartient à la catégorie spécifique
                if (
                    $critere->categorie_critere &&
                    $critere->categorie_critere->slug === 'grille-analyse-multi-critere'
                ) {
                    $isEligible = true;
                }

                if (!$isEligible) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le critère "' . $critere->intitule . '" n\'est pas éligible pour l\'analyse multicritere.',
                    ], 400);
                }

                $notation = Notation::where("id", $reponse['notation_id'])->where("categorie_critere_id", $critere->categorie_critere_id)->first();

                if (!$notation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Notation non trouvé avec l\'ID: ' . $reponse['notation_id'],
                    ], 400);
                }

                $data = [
                    'evaluateur_id' => $evaluateurId,
                    'commentaire' => $reponse['commentaire'] ?? null,
                    'is_auto_evaluation' => false,
                    'est_archiver' => false,
                    'updated_at' => now()
                ];

                if (!$isEvalClimatique) {
                    $data['notation_id'] = $notation->id;
                    $data['note'] = $notation->valeur;
                }

                EvaluationCritere::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'critere_id' => $critere->id,
                        'categorie_critere_id' => $critere->categorie_critere_id,
                    ],
                    $data
                    /*
                        [
                            'notation_id' => $notation->id,
                            'evaluateur_id' => $evaluateurId,
                            'note' => $notation->valeur,
                            'commentaire' => $reponse['commentaire'] ?? null,
                            'is_auto_evaluation' => false,
                            'est_archiver' => false,
                            'updated_at' => now(),
                        ]
                    */
                );
            }

            $evaluation->refresh();

            $finalResults = new Collection([]);

            if ($evaluation) {
                $aggregatedScores = $evaluation->getAMCAggregatedScores();
                $finalResults = $this->calculateFinalResults($aggregatedScores, 'amc');
            }

            // Récupérer les réponses de l'évaluateur connecté
            $evaluateurReponses = EvaluationCritere::forEvaluation($evaluation->id)
                ->evaluationExterne()
                ->active()
                ->with(['critere', 'notation'])
                ->get();

            $evaluation->update([
                "type_evaluation" => "amc",
                "statut"  => 1,
                "date_fin_evaluation" => now(),
                'resultats_evaluation' => [...$finalResults, ...["score_amc" => collect($finalResults['scores_ponderes_par_critere'])->avg("score_pondere")]],
                'evaluation' => [...($evaluation->evaluation ?? []), "amc" => EvaluationCritereResource::collection($evaluateurReponses)],
                'valider_le' => now(),
                'statut' => 1  // Marquer comme terminée
            ]);

            $grilleEvaluation = $this->categorieCritereRepository->getCanevasAMC();

            $ideeProjet->update([
                'score_amc' => collect($finalResults["scores_ponderes_par_critere"])->avg("score_pondere"),
                'statut' => StatutIdee::VALIDATION,  // Marquer comme terminée
                'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION),
                'canevas_amc' => $grilleEvaluation ? (new CategorieCritereResource($grilleEvaluation))->toArray(request()) : null,
            ]);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($ideeProjet, StatutIdee::VALIDATION);
            $this->enregistrerDecision($ideeProjet, 'Analyse multicritères complétée', 'Score AMC: ' . collect($finalResults["scores_ponderes_par_critere"])->avg("score_pondere"));

            $evaluation->refresh();
            DB::commit();

            // Notifier le Responsable hiérarchique du même ministère et le Responsable projet du résultat de l'AMC
            $responsableProjet = $ideeProjet->responsable;
            $responsablesHierarchiques = User::where('type', 'responsable-hierachique')
                ->where('profilable_type', get_class($ideeProjet->ministere))
                ->where('profilable_id', $ideeProjet->ministere->id)
                ->get();

            $servicesTechniques = User::whereHas('roles', function ($query) {
                $query->whereIn('slug', ['service-technique', 'service-etude']);
            })->get();

            $destinatairesResultatAMC = collect([$responsableProjet])->merge($responsablesHierarchiques)->merge($servicesTechniques);
            Notification::send($destinatairesResultatAMC, new ResultatAMCNotification($ideeProjet, $evaluation));

            // Notifier l'analyste DGPD pour la validation finale
            $analystesDGPD = User::where('type', 'analyste-dgpd')->get();
            if ($analystesDGPD->count() > 0) {
                Notification::send($analystesDGPD, new FinAMCAnalysteNotification($ideeProjet, $evaluation));
            }

            // Notifier le Comité de validation ministériel
            $comiteValidation = User::whereHas('roles', function ($query) {
                $query->where('slug', 'comite-validation-ministeriel');
            })->get();
            if ($comiteValidation->count() > 0) {
                Notification::send($comiteValidation, new ComiteValidationMinisterielNotification($ideeProjet, $evaluation));
            }

            return response()->json([
                'success' => true,
                'message' => 'Réponses d\'évaluation climatique soumises avec succès',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'reponses_soumises' => count($reponses),
                    'evaluateur_reponses' => EvaluationCritereResource::collection($evaluateurReponses),
                    'evaluateur_stats' => [
                        'total_criteres' => $evaluateurReponses->count(),
                        'criteres_evalues' => $evaluateurReponses->filter->isCompleted()->count(),
                        'criteres_en_attente' => $evaluateurReponses->filter->isPending()->count(),
                        'taux_completion' => $evaluateurReponses->count() > 0 ?
                            (($evaluateurReponses->filter->isCompleted()->count() / $evaluateurReponses->count()) * 100) : 0
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Évaluation climatique non trouvée pour cette idée de projet',
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e ? $e->getMessage() : 'Erreur lors de la soumission des réponses',
                'error' => $e->getMessage()
            ], $e ? $e->getCode() : 500);
        }
    }

    /**
     * Dashboard pour responsable : informations complètes de l'évaluation climatique.
     */
    public function getDashboardAMC($ideeProjetId): JsonResponse
    {
        try {

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (($ideeProjet->statut == StatutIdee::BROUILLON)) {
                throw new Exception("Veuillez effectuer l'auto evaluation climatique en interne", 403);
            } else if (($ideeProjet->statut == StatutIdee::IDEE_DE_PROJET)) {
                throw new Exception("Veuillez faire valider l'idee de projet en interne par un responsable hierachique", 403);
            }

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $evaluationClimatique = $ideeProjet->evaluations()
                ->where('type_evaluation', 'climatique')
                ->first();

            if (!$evaluationClimatique) {
                throw new Exception("Veuillez effectuer l'auto evaluation climatique en interne", 403);
            } else if ($evaluationClimatique->statut != 1) {
                throw new Exception("L'auto evaluation climatique en interne est toujours en cours veuillez a l'effectuer en premier", 403);
            }

            $evaluation = $ideeProjet->evaluations()
                ->where('type_evaluation', 'amc')->where("statut", 1)
                ->first();

            /* if (($ideeProjet->statut != StatutIdee::AMC || $ideeProjet->statut != StatutIdee::ANALYSE) && $evaluation->statut == 1) {
                throw new Exception("AMC deja effectuer", 403);
            } else if (($ideeProjet->statut != StatutIdee::AMC || $ideeProjet->statut != StatutIdee::ANALYSE) && $evaluation->statut == ) {
                throw new Exception("l'AMC ne peut etre appliquer a cette idee de projet", 403);
            } */

            if (!$evaluationClimatique) {
                throw new Exception("Aucune evaluation climatique n'a ete effectuer en interne pour cette idee de projet. Veuillez notifier", 403);
            }

            $critereClimatiqueEvaluer = $evaluationClimatique->evaluationCriteres()->evaluationExterne()->active()
                ->with(['critere', 'notation', 'categorieCritere'])->get();

            //$outilClimatique = CategorieCritere::with("criteres")->where("slug", 'evaluation-preliminaire-multi-projet-impact-climatique')->first();

            $score_pondere_par_critere = [];

            //$scoreClimatique = 0;
            $score_pondere_par_critere = new Collection([]);
            /*if ($outilClimatique->criteres->count()) {
                $evaluation_climatique = $outilClimatique->criteres->map(function ($critereClimatique) use ($evaluationClimatique) {
                    return $critereClimatique->critereEvaluations()->evaluationExterne()->active()->where("critere_id", $critereClimatique->id)->where("evaluation_id", $evaluationClimatique->id)->get()->map(function ($critereEval) {
                        return $critereEval;
                        $critere = $critereEvaluations->first()->critere;

                        $notes = $critereEvaluations->pluck('notation.valeur')->filter();

                        $moyenne_evaluateurs = $notes->average();

                        return [
                            'id' => $critereEvaluations->id,
                            'note' => $critereEvaluations->note,
                            'commentaire' => $critereEvaluations->commentaire,
                            'is_completed' => $critereEvaluations->isCompleted(),
                            'is_pending' => $critereEvaluations->isPending(),
                            'status' => $critereEvaluations->status,
                            'numeric_value' => $critereEvaluations->getNumericValue(),

                            'critere' => $critere,
                            'critere' => $critereEvaluations->notation,
                            'ponderation' => $critere->ponderation,
                            'ponderation_pct' => $critere->ponderation . '%',
                            'score_pondere' => $moyenne_evaluateurs * ($critere->ponderation / 100)
                        ];
                    });
                }); // Retire les valeurs "vides" (null, '', 0, false);

            }*/

            if ($critereClimatiqueEvaluer->count()) {
                $score_pondere_par_critere = $critereClimatiqueEvaluer->groupBy('critere_id')->map(function ($critereEvaluations) {
                    $critere = $critereEvaluations->first()->critere;

                    $notes = $critereEvaluations->pluck('notation.valeur')->filter();

                    $moyenne_evaluateurs = $notes->average();
                    return [
                        'critere' => $critere,
                        'ponderation' => $critere->ponderation,
                        'ponderation_pct' => $critere->ponderation . '%',
                        'score_pondere' => $moyenne_evaluateurs * ($critere->ponderation / 100)
                    ];
                });
            }

            $categorie = CategorieCritere::with("criteres")->where("slug", 'grille-analyse-multi-critere')->first();

            if ($evaluation) {
                $aggregatedScores = $evaluation->getAMCAggregatedScores();
            } else {

                $aggregatedScores = $categorie->criteres->map(function ($critere) {
                    return [
                        'critere' => $critere,
                        'ponderation' => $critere->ponderation,
                        'ponderation_pct' => $critere->ponderation . '%',
                        'moyenne_evaluateurs' => 0,
                        'score_pondere' => 0
                    ];
                });
            }

            $finalResults = $this->calculateFinalResults($aggregatedScores, 'amc');

            return response()->json([
                'success' => true,
                'data' => [
                    "idee_projet" => new IdeesProjetResource($ideeProjet),
                    "evaluation" => $evaluation,
                    'evaluation_climatique' => [
                        "score_climatique" => ($score_pondere_par_critere->sum('score_pondere') / $categorie->criteres->count()),
                        "scores_pondere_par_critere" => array_values($score_pondere_par_critere->toArray()),/*  EvaluationCritereResource::collection($critereClimatiqueEvaluer)->resource->toArray()) */
                        "evaluation_effectuer" => EvaluationCritereResource::collection($critereClimatiqueEvaluer)
                    ],
                    'evaluation_amc' => $evaluation ? new EvaluationResource($evaluation) : null,
                    ...$finalResults,
                    "resultats_evaluation" => $evaluation ? $evaluation["resultats_evaluation"] : null,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e ? $e->getMessage() : 'Erreur lors de la récupération du dashboard',
                'error' => $e->getMessage()
            ], $e ? $e->getCode() : 500);
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

    /**
     * Classement des idées de projet par secteur et catégorie selon score pondéré AMC
     */
    public function getClassementIdeesProjetsValidation(): JsonResponse
    {
        try {
            // Récupérer toutes les idées de projet en statut "Validation" avec leurs relations
            $ideesProjets = IdeeProjet::with(['secteur', 'categorie', 'responsable', 'ministere'])
                ->where('statut', StatutIdee::VALIDATION)
                ->whereNotNull('score_amc')
                ->get();

            if ($ideesProjets->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aucune idée de projet en validation trouvée',
                    'data' => []
                ]);
            }

            // Récupérer tous les critères AMC disponibles
            $criteresAMC = Critere::whereHas('categorie_critere', function ($query) {
                $query->where('slug', 'grille-analyse-multi-critere');
            })->get();

            // Traiter chaque idée projet avec ses données d'évaluation complètes
            $ideesProjetsAvecEvaluations = $ideesProjets->map(function ($idee) use ($criteresAMC) {
                // Récupérer l'évaluation AMC de cette idée
                $evaluationAMC = $idee->evaluations()
                    ->where('type_evaluation', 'amc')
                    ->where('statut', 1)
                    ->first();

                $evaluationCriteres = [];
                $scoresPonderes = [];

                if ($evaluationAMC) {
                    // Récupérer les évaluations de critères existantes
                    $evaluationCriteresExistants = $evaluationAMC->evaluationCriteres()
                        ->with(['critere', 'notation', 'evaluateur'])
                        ->active()
                        ->get()
                        ->keyBy('critere_id');

                    // Pour chaque critère AMC, récupérer ou créer les données d'évaluation
                    foreach ($criteresAMC as $critere) {
                        $evaluation = $evaluationCriteresExistants->get($critere->id);

                        if ($evaluation && $evaluation->notation) {
                            $valeurNotation = $evaluation->notation->valeur ?? 0;
                        } else {
                            $valeurNotation = 0; // Critère manquant = 0
                        }

                        $scorePondere = $valeurNotation * ($critere->ponderation / 100);

                        $evaluationCriteres[] = [
                            'critere_id' => $critere->id,
                            'critere_nom' => $critere->intitule,
                            'ponderation' => $critere->ponderation,
                            'valeur_notation' => $valeurNotation,
                            'score_pondere' => round($scorePondere, 2),
                            'commentaire' => $evaluation->commentaire ?? null,
                            'evaluateur' => $evaluation ? $evaluation->evaluateur?->nom : null,
                            'date_evaluation' => $evaluation ? $evaluation->updated_at->format('d/m/Y') : null,
                            'est_complete' => $evaluation ? !$evaluation->isPending() : false
                        ];

                        $scoresPonderes[] = $scorePondere;
                    }
                }

                // Ajouter les données d'évaluation à l'idée
                return $idee->setAppends([])->toArray() + [
                    'score_amc_calcule' => round(array_sum($scoresPonderes), 2),
                    'secteur_nom' => $idee->secteur?->nom ?? 'Sans secteur',
                    'categorie_nom' => $idee->categorie?->categorie ?? 'Sans catégorie',
                    'responsable_nom' => $idee->responsable?->nom ?? 'Non assigné',
                    'ministere_nom' => $idee->ministere?->nom ?? 'Non assigné',
                    'evaluation_criteres' => $evaluationCriteres,
                    'evaluation_id' => $evaluationAMC?->id,
                    'nombre_criteres_evalues' => count(array_filter($evaluationCriteres, fn($e) => $e['est_complete'])),
                    'nombre_criteres_total' => $criteresAMC->count()
                ];
            });

            // Trier par score AMC décroissant
            $ideesProjetsTriees = $ideesProjetsAvecEvaluations->sortByDesc('score_amc_calcule');

            // Grouper par secteur puis par catégorie
            $classementParSecteur = $ideesProjetsTriees->groupBy('secteur_nom')->map(function ($ideesPourSecteur, $secteurNom) {

                $categoriesClassement = $ideesPourSecteur->groupBy('categorie_nom')->map(function ($ideesPourCategorie, $categorieNom) {

                    // Trier par score AMC décroissant au sein de chaque catégorie
                    $ideesTriees = $ideesPourCategorie->sortByDesc('score_amc_calcule')->values();

                    return [
                        'categorie' => $categorieNom,
                        'nombre_idees' => $ideesTriees->count(),
                        'score_moyen' => round($ideesTriees->avg('score_amc_calcule'), 2),
                        'score_max' => round($ideesTriees->max('score_amc_calcule'), 2),
                        'score_min' => round($ideesTriees->min('score_amc_calcule'), 2),
                        'idees' => $ideesTriees->map(function ($idee, $index) {
                            return [
                                'rang_dans_categorie' => $index + 1,
                                'id' => $idee['id'],
                                'titre_projet' => $idee['titre_projet'],
                                'score_amc' => $idee['score_amc_calcule'],
                                'score_climatique' => round($idee['score_climatique'] ?? 0, 2),
                                'responsable' => $idee['responsable_nom'],
                                'ministere' => $idee['ministere_nom'],
                                'secteur_id' => $idee['secteurId'],
                                'categorie_id' => $idee['categorieId'],
                                'evaluation_id' => $idee['evaluation_id'],
                                'nombre_criteres_evalues' => $idee['nombre_criteres_evalues'],
                                'nombre_criteres_total' => $idee['nombre_criteres_total'],
                                'taux_completion' => round(($idee['nombre_criteres_evalues'] / $idee['nombre_criteres_total']) * 100, 1),
                                'created_at' => $idee['created_at'],
                                'evaluation_criteres' => $idee['evaluation_criteres']
                            ];
                        })->toArray()
                    ];
                })->sortByDesc('score_moyen')->values();

                return [
                    'secteur' => $secteurNom,
                    'nombre_categories' => $categoriesClassement->count(),
                    'nombre_total_idees' => $ideesPourSecteur->count(),
                    'score_moyen_secteur' => round($ideesPourSecteur->avg('score_amc_calcule'), 2),
                    'categories' => $categoriesClassement->toArray()
                ];
            })->sortByDesc('score_moyen_secteur')->values();

            // Ajouter le classement global
            $classementGlobal = $ideesProjetsTriees->map(function ($idee, $index) {
                return [
                    'rang_global' => $index + 1,
                    'id' => $idee['id'],
                    'titre_projet' => $idee['titre_projet'],
                    'secteur' => $idee['secteur_nom'],
                    'categorie' => $idee['categorie_nom'],
                    'score_amc' => $idee['score_amc_calcule'],
                    'score_climatique' => round($idee['score_climatique'] ?? 0, 2),
                    'responsable' => $idee['responsable_nom'],
                    'ministere' => $idee['ministere_nom'],
                    'taux_completion' => round(($idee['nombre_criteres_evalues'] / $idee['nombre_criteres_total']) * 100, 1)
                ];
            })->values()->toArray();

            // Statistiques générales
            $statistiques = [
                'nombre_total_idees' => $ideesProjetsTriees->count(),
                'nombre_secteurs' => $classementParSecteur->count(),
                'nombre_categories' => $ideesProjetsTriees->groupBy('categorie_nom')->count(),
                'score_amc_moyen_global' => round($ideesProjetsTriees->avg('score_amc_calcule'), 2),
                'score_amc_max' => round($ideesProjetsTriees->max('score_amc_calcule'), 2),
                'score_amc_min' => round($ideesProjetsTriees->min('score_amc_calcule'), 2),
                'nombre_criteres_amc' => $criteresAMC->count(),
                'derniere_mise_a_jour' => now()->format('d/m/Y H:i:s')
            ];

            return response()->json([
                'success' => true,
                'message' => 'Classement généré avec succès',
                'data' => [
                    'statistiques' => $statistiques,
                    'classement_global' => $classementGlobal,
                    'classement_par_secteur' => $classementParSecteur->toArray()
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du classement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ===================== ÉVALUATION DE PERTINENCE =====================

    /**
     * Soumettre une évaluation de pertinence
     */
    public function soumettreEvaluationPertinence(array $data, $ideeProjetId): JsonResponse
    {
        try {

            DB::beginTransaction();

            $evaluateurId = auth()->id();
            $reponses = $data['reponses'];

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier si l'idée de projet est soumise - si non, refuser l'évaluation
            if ($ideeProjet->est_soumise !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'évaluation de pertinence ne peut être effectuée que sur des idées de projet soumises.',
                ], 403);
            }

            $evaluation = Evaluation::where(
                'projetable_id',
                $ideeProjet->id
            )->where(
                'projetable_type',
                get_class($ideeProjet)
            )
                ->where('type_evaluation', 'pertinence')
                ->first();

            if ($ideeProjet->statut != StatutIdee::BROUILLON && ($evaluation?->statut == 1 && $evaluation?->date_fin_evaluation != null)) {
                throw new Exception("Evaluation de pertinence deja termine", 403);
            }

            $is_auto_evaluation = true;

            $evaluation = Evaluation::updateOrCreate([
                'projetable_id' => $ideeProjet->id,
                'projetable_type' => get_class($ideeProjet),
                "type_evaluation" => "pertinence"
            ], [
                "type_evaluation" => "pertinence",
                "statut"  => $is_auto_evaluation ? 0 : 1,
                "date_fin_evaluation" => $is_auto_evaluation ? null : now()
            ]);

            $isAssigned = false;
            if ($is_auto_evaluation) {
                if ((auth()->user()->profilable_type == Organisation::class || auth()->user()->profilable_type == Dpaf::class) && auth()->user()->profilable?->ministere && $ideeProjet->ministere && (auth()->user()->profilable->ministere?->id == $ideeProjet->ministere->id)) {
                    $isAssigned = $evaluation->evaluateursPertinence()->where("id", auth()->id())->first()?->hasPermissionTo('effectuer-evaluation-pertinence-idee-projet');
                }
            }

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas assigné comme évaluateur pour cette évaluation de pertinence.',
                ], 403);
            }

            // Vérifier et mettre à jour les réponses
            foreach ($reponses as $reponse) {
                // Vérifier que le critère appartient à la bonne catégorie ou est obligatoire
                $critere = Critere::with('categorie_critere')->find($reponse['critere_id']);

                if (!$critere) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Critère non trouvé avec l\'ID: ' . $reponse['critere_id'],
                    ], 400);
                }

                // Vérifier les conditions d'éligibilité du critère
                $isEligible = false;

                // Cas 1: Le critère appartient à la catégorie spécifique
                if (
                    $critere->categorie_critere &&
                    $critere->categorie_critere->slug === 'grille-evaluation-pertinence-idee-projet'
                ) {
                    $isEligible = true;
                }

                if (!$isEligible) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le critère "' . $critere->intitule . '" n\'est pas éligible pour cette évaluation de pertinence.',
                    ], 400);
                }

                $notation = Notation::find($reponse['notation_id']);

                if (!$notation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Notation non trouvé avec l\'ID: ' . $reponse['notation_id'],
                    ], 400);
                }

                $evaluationCritere = EvaluationCritere::where([
                    'evaluation_id' => $evaluation->id,
                    'evaluateur_id' => $evaluateurId,
                    'categorie_critere_id' => $reponse["categorie_critere_id"],
                    'critere_id' => $reponse['critere_id'],
                    'est_archiver' => false, // ← ici la condition
                    'is_auto_evaluation' => $is_auto_evaluation,
                ])->first();


                if ($evaluationCritere) {
                    $evaluationCritere->fill([
                        'notation_id' => $notation->id,
                        'note' => $notation->valeur,
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'is_auto_evaluation' => $is_auto_evaluation,
                        'updated_at' => now(),
                    ]);
                    $evaluationCritere->save();
                } else {
                    $evaluationCritere = EvaluationCritere::create([
                        'evaluation_id' => $evaluation->id,
                        'evaluateur_id' => $evaluateurId,
                        'categorie_critere_id' => $reponse["categorie_critere_id"],
                        'critere_id' => $reponse['critere_id'],
                        'notation_id' => $notation->id,
                        'note' => $notation->valeur,
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'is_auto_evaluation' => $is_auto_evaluation,
                        'est_archiver' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $evaluation->refresh();

            if ($evaluation->statut == -1) {

                $evaluation->statut = 0;

                $evaluation->save();
            }

            // Récupérer les réponses de l'évaluateur connecté
            $evaluationPertinenceReponses = EvaluationCritere::forEvaluation($evaluation->id)
                ->autoEvaluation()
                ->active()
                ->byEvaluateur($evaluateurId)
                ->with(['critere', 'notation', 'categorieCritere'])
                ->get();

            if ($evaluation->statut == 0) {
                $evaluation->update([
                    "evaluation" => EvaluationCritereResource::collection($evaluationPertinenceReponses),
                    //"resultats_evaluation" => []
                ]);
            }

            DB::commit();

            // Calculer la progression et notifier le Responsable projet
            $tauxProgression = $this->calculateCompletionPercentage($evaluation, "pertinence");
            $responsableProjet = $ideeProjet->responsable;

            if ($responsableProjet && $is_auto_evaluation) {
                // Note: Ajouter les notifications pour pertinence si nécessaire
                // $scorePertinence = $ideeProjet->score_pertinence;
                // Notification::send($responsableProjet, new ProgressionEvaluationPertinenceNotification($ideeProjet, $evaluation, $tauxProgression, $scorePertinence));

                // Si c'est le dernier évaluateur qui termine (progression = 100%)
                // if ($tauxProgression >= 100) {
                //     $scorePertinenceFinal = $this->calculateScorePertinence($evaluation->id)['score_final_pondere'] ?? 0;
                //     Notification::send($responsableProjet, new EvaluationPertinenceTermineNotification($ideeProjet, $evaluation, $scorePertinenceFinal));
                // }
            }

            return response()->json([
                'success' => true,
                'message' => 'Réponses d\'évaluation de pertinence soumises avec succès',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'reponses_soumises' => count($reponses),
                    'evaluateur_reponses' => EvaluationCritereResource::collection($evaluationPertinenceReponses),
                    'evaluateur_stats' => [
                        'total_criteres' => $evaluationPertinenceReponses->count(),
                        'criteres_evalues' => $evaluationPertinenceReponses->filter->isCompleted()->count(),
                        'criteres_en_attente' => $evaluationPertinenceReponses->filter->isPending()->count(),
                        'taux_completion' => $evaluationPertinenceReponses->count() > 0 ?
                            (($evaluationPertinenceReponses->filter->isCompleted()->count() / $evaluationPertinenceReponses->count()) * 100) : 0
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Évaluation de pertinence non trouvée pour cette idée de projet',
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e ? $e->getMessage() : 'Erreur lors de la soumission des réponses',
                'error' => $e->getMessage()
            ], $e ? ($e->getCode() ?? 500) : 500);
        }
    }

    /**
     * Finaliser une auto-évaluation de pertinence
     */
    public function finaliserAutoEvaluationPertinence($ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'responsable-projet') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier que l'évaluation pertinence existe
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'pertinence')
                ->firstOrFail();

            if ($evaluation->statut == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Score Auto Evaluation pertinence déja enregistré',
                ], 400);
            }

            if (auth()->id() !== $ideeProjet->responsable->id) {
                throw new Exception("Vous n'avez pas les droits pour effectuer cette action", 403);
            }

            $completionPercentage = $this->calculateCompletionPercentage($evaluation, "pertinence");

            if ($completionPercentage != 100) {
                throw new Exception("Auto-evaluation pertinence toujours en cours, veuillez patienter", 403);
            }

            // Calculer les résultats finaux
            //$aggregatedScores = $evaluation->getAggregatedScores();

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation de pertinence
            $evaluateurs = $evaluation->evaluateursPertinence()->get();
            $criteres = Critere::whereHas('categorie_critere', function ($query) {
                $query->where('slug', 'grille-evaluation-pertinence-idee-projet');
            })->get();
            $evaluationCriteres = new Collection();

            foreach ($evaluateurs as $evaluateur) {
                foreach ($criteres as $critere) {
                    $evaluationCritere = $evaluation->evaluationCriteres()
                        ->autoEvaluation()
                        ->active()
                        ->where('critere_id', $critere->id)
                        ->where('evaluateur_id', $evaluateur->id)
                        ->firstOrNew([
                            'evaluation_id' => $evaluation->id,
                            'critere_id' => $critere->id,
                            'evaluateur_id' => $evaluateur->id,
                        ], [
                            'categorie_critere_id' => $critere->categorie_critere_id,
                            'note' => 'En attente',
                            'notation_id' => null,
                            'is_auto_evaluation' => true,
                            'est_archiver' => false
                        ]);

                    $evaluationCriteres->push($evaluationCritere->load(['critere', 'notation', 'categorieCritere', 'evaluateur']));
                }
            }

            $aggregatedScores = $evaluation->aggregateScoresByCritere($evaluationCriteres);
            $finalResults = $this->calculateFinalResults($aggregatedScores, "pertinence");

            // Calculer et ajouter les scores
            $scoreGlobal = $this->calculateScoreGlobal($evaluation->id);
            $finalResults['score_global'] = $scoreGlobal;

            // Ajouter score pertinence si c'est une évaluation de pertinence
            if ($evaluation->type_evaluation === 'pertinence') {
                $scorePertinence = $this->calculateScorePertinence($evaluation->id);
                $finalResults['score_pertinence'] = $scorePertinence;
            }

            $grilleEvaluation = CategorieCritere::where('slug', 'grille-evaluation-pertinence-idee-projet')->first();

            $ideeProjet->update([
                'score_pertinence' => $finalResults['score_final_pondere'],
                'est_coherent' => true,
                //'statut' => StatutIdee::IDEE_DE_PROJET,  // Marquer comme terminée

                //'phase' => $this->getPhaseFromStatut(StatutIdee::IDEE_DE_PROJET),
                //'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::IDEE_DE_PROJET),

                // Enregistrer le canevas pertinence dans l'idée projet
                /**
                 * Enregistrement du canevas utiliser pour l'evaluation de pertinence dans l'idée projet
                 */
                'canevas_pertinence' => $grilleEvaluation ? (new CategorieCritereResource($grilleEvaluation))->toArray(request()) : null,
            ]);

            // Enregistrer le workflow et la décision
            //$this->enregistrerWorkflow($ideeProjet, StatutIdee::IDEE_DE_PROJET);
            $this->enregistrerDecision($ideeProjet, 'Finalisation score pertinence', 'Score pertinence finalisé: ' . ($finalResults['score_final_pondere'] ?? 0));

            $evaluation->update([
                'resultats_evaluation' => $finalResults,
                'valider_le' => now(),
                'statut' => 1  // Marquer comme terminée
            ]);

            // Notifier le Responsable que l'évaluation de pertinence est terminée
            // Note: Ajouter les notifications pour pertinence si nécessaire
            // $responsable = $ideeProjet->responsable;
            // if ($responsable) {
            //     Notification::send($responsable, new EvaluationPertinenceFinaliseeNotification($ideeProjet, $evaluation));
            // }

            // Notifier le Responsable hiérarchique qu'une nouvelle idée de projet a été créée
            // $responsablesHierarchiques = User::where('type', 'responsable-hierachique')
            //     ->where('profilable_type', get_class($ideeProjet->ministere))
            //     ->where('profilable_id', $ideeProjet->ministere->id)
            //     ->get();
            //
            // if ($responsablesHierarchiques->count() > 0) {
            //     $scorePertinence = $finalResults['score_final_pondere'] ?? 0;
            //     Notification::send($responsablesHierarchiques, new NouvellePertinenceNotification($ideeProjet, $scorePertinence));
            // }

            $evaluation->refresh();

            return response()->json([
                'success' => true,
                'message' => "Score de l'auto-Évaluation de pertinence finalisée avec succès",
                'data' => $evaluation
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "'Erreur lors de l'enregistrement du score. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refaire une auto-évaluation de pertinence
     */
    public function refaireAutoEvaluationPertinence($ideeProjetId): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (auth()->user()->type !== 'responsable-projet') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            if (auth()->id() !== $ideeProjet->responsable->id) {
                throw new Exception("Vous n'avez pas les droits pour effectuer cette action", 403);
            }

            // Vérifier que l'évaluation pertinence existe
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'pertinence')
                ->firstOrFail();

            if ($evaluation->statut == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Auto Evaluation pertinence déja validé',
                ], 400);
            }

            $completionPercentage = $this->calculateCompletionPercentage($evaluation, "pertinence");

            if ($completionPercentage != 100) {
                throw new Exception("Auto-evauation pertinence toujours en cours, veuillez patienter", 403);
            }

            $criteresEvaluation = $evaluation->evaluationCriteres()
                ->autoEvaluation()
                ->active()->get();

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation pertinence
            $evaluateurs = $evaluation->evaluateursPertinence()->get();


            if ($evaluateurs->count() == 0) {
                throw new Exception('Aucun évaluateur trouvé avec la permission "effectuer-evaluation-pertinence-idee-projet"', 404);
            }

            $ideeProjet->update([
                'est_soumise' => false,
                'statut' => StatutIdee::BROUILLON,  // Marquer comme terminée
                'phase' => $this->getPhaseFromStatut(StatutIdee::BROUILLON),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::BROUILLON),
            ]);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($ideeProjet, StatutIdee::BROUILLON);
            $this->enregistrerDecision($ideeProjet, 'Réévaluation pertinence demandée', 'Score pertinence insatisfaisant - Retour en phase de rédaction');

            $evaluation->update([
                'resultats_evaluation' => [],
                'evaluation' => [],
                'valider_le' => null,
                'statut' => 0  // Marquer comme en cours
            ]);

            $criteresEvaluation->each->update(["est_archiver" => true]);

            DB::commit();

            // Notifier les évaluateurs de la décision sur le faible score pertinence
            // Note: Si vous avez une notification spécifique pour la pertinence, ajoutez-la ici
            // Notification::send($evaluateurs, new DecisionFaibleScorePertinenceNotification($ideeProjet, $scorePertinence, 'reevaluer', 'Score pertinence insatisfaisant - Réévaluation demandée'));

            return response()->json([
                'success' => true,
                'message' => 'Score pertinence insatisfaisant, Invitation pertinence renvoyée',
                'data' => null
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la relance de l'evaluation pertinence. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer le score de pertinence
     */
    public function calculateScorePertinence(int $evaluationId): array
    {
        $evaluation = Evaluation::where('id', $evaluationId)
            ->where('type_evaluation', 'pertinence')
            ->firstOrFail();

        // Récupérer tous les critères complétés pour cette évaluation
        $criteres = EvaluationCritere::forEvaluation($evaluation->id)
            ->autoEvaluation()
            ->active()
            ->completed()
            ->with(['critere', 'notation'])
            ->get();

        if ($criteres->isEmpty()) {
            return [
                'score_climatique' => 0,
                'score_pourcentage' => 0,
                'nombre_criteres_evalues' => 0,
                'ponderation_totale' => 0,
                'criteres_details' => [],
                'statut_evaluation' => 'Aucun critère évalué'
            ];
        }

        // Grouper par critère et calculer la moyenne pour chaque critère
        $criteresMoyennes = $criteres->groupBy('critere_id')->map(function ($critereEvaluations) {
            $critere = $critereEvaluations->first()->critere;
            $notes = $critereEvaluations->pluck('notation.valeur')->filter()->map(function ($note) {
                return is_numeric($note) ? (float) $note : 0;
            });

            $moyenne = $notes->average();
            $ponderation = $critere->ponderation ?? 0;

            return [
                'critere_id' => $critere->id,
                'critere_nom' => $critere->intitule,
                'moyenne_critere' => $moyenne,
                'ponderation' => $ponderation,
                'score_pondere' => $moyenne * ($ponderation / 100),
                'nombre_evaluateurs' => $critereEvaluations->count(),
                'notes_individuelles' => $notes->toArray()
            ];
        });

        // Calculer le score climatique global
        $scoreTotal = $criteresMoyennes->sum('score_pondere');
        $ponderationTotale = $criteresMoyennes->sum('ponderation');

        // Score climatique sur l'échelle utilisée (généralement sur 5)
        $scoreClimatique = $ponderationTotale > 0 ?
            (($scoreTotal * 100) / $ponderationTotale) : 0;

        return [
            //'score_climatique' => $scoreClimatique,
            "score_climatique" => ($criteresMoyennes->avg('score_pondere')),
            'nombre_criteres_evalues' => $criteresMoyennes->count(),
            'ponderation_totale' => $ponderationTotale,
            'criteres_details' => $criteresMoyennes->values()->toArray(),
            'statut_evaluation' => $this->getStatutScoreClimatique($scoreClimatique)
        ];

        $score_brut = 0;
        $score_pondere = 0;
        $total_ponderation = 0;
        $nombre_criteres = $criteres->count();

        foreach ($criteres as $evaluationCritere) {
            $critere = $evaluationCritere->critere;
            $notation = $evaluationCritere->notation;

            if ($critere && $notation) {
                $ponderation = $critere->ponderation ?? 1;
                $valeur_notation = is_numeric($notation->valeur) ? floatval($notation->valeur) : 0;

                dump($valeur_notation);
                $score_brut += $valeur_notation;
                $score_pondere += ($valeur_notation * $ponderation);
                $total_ponderation += $ponderation;
            }
        }

        // Calcul du score final pondéré (sur 100)
        $score_final_pondere = $total_ponderation > 0
            ? ($score_pondere / $total_ponderation) * 100
            : 0;


        dump($valeur_notation);

        return [
            'score_brut' => $score_brut,
            'score_pondere' => $score_pondere,
            'score_final_pondere' => round($score_final_pondere, 2),
            'total_ponderation' => $total_ponderation,
            'nombre_criteres' => $nombre_criteres,
            'statut_pertinence' => $this->getStatutScorePertinence($score_final_pondere)
        ];
    }

    /**
     * Obtenir le score de pertinence
     */
    public function getScorePertinence(int $evaluationId): JsonResponse
    {
        try {
            $evaluation = Evaluation::where('id', $evaluationId)
                ->where('type_evaluation', 'pertinence')
                ->firstOrFail();

            $scoreData = $this->calculateScorePertinence($evaluationId);

            return response()->json([
                'success' => true,
                'message' => 'Score de pertinence calculé avec succès',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'score_data' => $scoreData,
                    'evaluation_terminee' => $evaluation->statut == 1,
                    'date_fin' => $evaluation->date_fin_evaluation
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtenir le dashboard d'évaluation de pertinence
     */
    public function getDashboardEvaluationPertinence($ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->profilable_type == Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acceder a cette resource", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if (auth()->user()->profilable?->ministere?->id !== $ideeProjet->ministere->id) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $evaluation = Evaluation::firstOrCreate([
                'projetable_id' => $ideeProjet->id,
                'projetable_type' => get_class($ideeProjet),
                "type_evaluation" => "pertinence"
            ], [
                "type_evaluation" => "pertinence",
                "statut"  => 0,
                "date_debut_evaluation" => now(),
                "evaluation" => [],
                "resultats_evaluation" => []
            ]);

            // Vérifier que c'est une évaluation de pertinence
            if ($evaluation->type_evaluation !== 'pertinence') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette évaluation n\'est pas de type pertinence'
                ], 400);
            }

            if ($evaluation->statut != 1) {
                // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation de pertinence
                $evaluateurs = $evaluation->evaluateursPertinence()->get();
            } else {

                $evaluateurs = $evaluation->evaluateurs()
                    ->wherePivot('is_auto_evaluation', true)
                    ->select('users.*')
                    ->distinct('users.id')
                    ->get();
            }

            if ($evaluateurs->count() == 0) {
                throw new Exception('Aucun évaluateur trouvé avec la permission "effectuer-evaluation-pertinence-idee-projet"', 404);
            }

            // Récupérer les critères éligibles pour l'évaluation de pertinence
            $criteres = Critere::whereHas('categorie_critere', function ($query) {
                $query->where('slug', 'grille-evaluation-pertinence-idee-projet');
            })->get();

            $evaluationCriteres = new Collection();

            // Assigner chaque évaluateur à tous les critères
            foreach ($evaluateurs as $evaluateur) {
                foreach ($criteres as $critere) {
                    $evaluationCritere = $evaluation->evaluationCriteres()
                        ->autoEvaluation()
                        ->active()
                        ->where('critere_id', $critere->id)
                        ->where('evaluateur_id', $evaluateur->id)
                        ->firstOrNew([
                            'evaluation_id' => $evaluation->id,
                            'critere_id' => $critere->id,
                            'evaluateur_id' => $evaluateur->id,
                        ], [
                            'categorie_critere_id' => $critere->categorie_critere_id,
                            'note' => 'En attente',
                            'notation_id' => null,
                            'is_auto_evaluation' => true,
                            'est_archiver' => false
                        ]);

                    $evaluationCriteres->push($evaluationCritere->load(['critere', 'notation', 'categorieCritere', 'evaluateur']));
                }
            }
            $aggregatedScores = $evaluation->aggregateScoresByCritere($evaluationCriteres);

            $finalResults = $this->calculateFinalResults($aggregatedScores, "pertinence");
            $completionPercentage = $this->calculateCompletionPercentage($evaluation, "pertinence");

            // Progression par évaluateur
            $progressionParEvaluateur = $this->calculateProgressionParEvaluateur($evaluationCriteres);

            // Statistiques générales
            $totalEvaluateurs = $evaluationCriteres->pluck('evaluateur_id')->unique()->count();
            $totalCriteres = $criteres->count();
            $totalEvaluationsCompletes = $evaluationCriteres->filter->isCompleted()->count();
            $totalEvaluationsAttendues = $totalEvaluateurs * $totalCriteres;

            return response()->json([
                'success' => true,
                'data' => [
                    "statut_idee" => $ideeProjet->statut,
                    "idee_projet" => new IdeesProjetResource($ideeProjet),
                    'evaluation' => new EvaluationResource($evaluation),

                    // Taux de progression global
                    'taux_progression_global' => [
                        'pourcentage' => $completionPercentage,
                        'evaluations_completes' => $totalEvaluationsCompletes,
                        'evaluations_attendues' => $totalEvaluationsAttendues,
                        'evaluateurs_total' => $totalEvaluateurs,
                        'criteres_total' => $totalCriteres
                    ],

                    // Progression par évaluateur
                    'progression_par_evaluateur' => $progressionParEvaluateur,

                    // Résultats agrégés et finaux
                    'final_results' => $finalResults
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?? 'Erreur lors de la récupération du dashboard',
                'error' => $e->getMessage()
            ], $e ? ($e->getCode() ?? 500) : 500);
        }
    }

    /**
     * Déterminer le statut basé sur le score de pertinence
     */
    private function getStatutScorePertinence(float $score): string
    {
        return match (true) {
            $score >= 70 => 'Très pertinent',
            $score >= 50 => 'Pertinent',
            $score >= 30 => 'Moyennement pertinent',
            default => 'Peu pertinent'
        };
    }

    /**
     * Mettre à jour le score de pertinence dans les résultats
     */
    public function updateScorePertinenceInResults(int $evaluationId): bool
    {
        try {
            $evaluation = Evaluation::where('id', $evaluationId)
                ->where('type_evaluation', 'pertinence')
                ->firstOrFail();
            $scoreData = $this->calculateScorePertinence($evaluationId);

            // Récupérer les résultats existants ou créer un nouveau tableau
            $resultatsExistants = $evaluation->resultats_evaluation ?? [];

            // Mettre à jour avec les nouvelles données de pertinence
            $resultatsExistants['score_pertinence'] = $scoreData['score_final_pondere'];
            $resultatsExistants['statut_pertinence'] = $scoreData['statut_pertinence'];
            $resultatsExistants['details_pertinence'] = $scoreData;

            // Sauvegarder les résultats mis à jour
            $evaluation->update([
                'resultats_evaluation' => $resultatsExistants,
                'score_pertinence' => $scoreData['score_final_pondere']
            ]);

            return true;
        } catch (Exception $e) {
            \Log::error('Erreur lors de la mise à jour du score de pertinence: ' . $e->getMessage());
            return false;
        }
    }
}
