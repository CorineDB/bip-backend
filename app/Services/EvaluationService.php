<?php

namespace App\Services;

use App\Enums\StatutIdee;
use App\Http\Requests\evaluations\ModifierEvaluationClimatiqueRequest;
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
use App\Notifications\EvaluationClimatiqueAssigneeNotification;
use App\Traits\GenerateUniqueId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use App\Events\IdeeProjetTransformee;

class EvaluationService extends BaseService implements EvaluationServiceInterface
{
    use GenerateUniqueId;
    protected BaseRepositoryInterface $repository;
    protected IdeeProjetRepositoryInterface $ideeProjetRepository;

    public function __construct(
        EvaluationRepositoryInterface $repository,
        IdeeProjetRepositoryInterface $ideeProjetRepository
    ) {
        parent::__construct($repository);
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
            if (auth()->user()->type !== 'responsable-hierachique' && auth()->user()->type !== 'super-admin' && auth()->user()->type !== 'organisation') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            /*if ($ideeProjet->statut != StatutIdee::IDEE_DE_PROJET) {
                throw new Exception("Vous le statut de l'idee de projet est a brouillon");
            }*/

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
                    'statut' => StatutIdee::ANALYSE
                ]);

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
                    'score_climatique' => 0,
                    'statut' => StatutIdee::BROUILLON
                ]);

                // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
                $evaluateurs = User::where('profilable_type', auth()->user()->profilable_type)->where('profilable_id', auth()->user()->profilable_id)
                    ->where(function ($query) {
                        $query->whereHas('roles', function ($query) {
                            $query->whereHas('permissions', function ($subQuery) {
                                $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                            });
                        })->orWhereHas('role', function ($query) {
                            $query->whereHas('permissions', function ($subQuery) {
                                $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                            });
                        });
                    })->get();

                if ($evaluateurs->count() == 0) {
                    throw new Exception('Aucun évaluateur trouvé avec la permission "effectuer-evaluation-climatique-idee-projet"', 404);
                }

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

                $evaluation->update([
                    'resultats_evaluation' => [],
                    'evaluation' => [],
                    'valider_le' => null,
                    'statut' => -1  // Marquer comme terminée
                ]);

                $criteresEvaluationClimatique->get()->each->update(["est_archiver" => true]);
            }

            $ideeProjet->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Idee de projet evaluer avec succès',
                'data' => $evaluation
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la validation de l'idee de projet",
                'error' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Finalize evaluation and calculate final results.
     */
    public function validationIdeeDeProjetAProjet($ideeProjetId, array $attributs): JsonResponse
    {
        try {
            if (auth()->user()->type !== 'analyste-dgpd' && auth()->user()->type !== 'super-admin' && auth()->user()->type !== 'organisation') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            /*if ($ideeProjet->statut->value != StatutIdee::VALIDATION->value) {
                throw new Exception("Vous le statut de l'idee de projet est a brouillon");
            }*/

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
                    'statut' => StatutIdee::NOTE_CONCEPTUEL
                ]);

                // Déclencher l'event pour dupliquer vers un projet seulement si validé
                event(new IdeeProjetTransformee($ideeProjet));
            } else {
                $ideeProjet->update([
                    'score_amc' => 0,
                    'statut' => StatutIdee::ANALYSE
                ]);

                $evaluation->update([
                    'resultats_evaluation' => [],
                    'evaluation' => [],
                    'valider_le' => null,
                    'statut' => -1  // Marquer comme terminée
                ]);

                // Vérifier que l'évaluation climatique existe
                $evaluationClimatique = Evaluation::where('projetable_type', get_class($ideeProjet))
                    ->where('projetable_id', $ideeProjet->id)
                    ->where('type_evaluation', 'climatique')
                    ->firstOrFail();

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

            $ideeProjet->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Idee de projet evaluer avec succès',
                'data' => $evaluation
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la validation de l'idee de projet",
                'error' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Finalize evaluation and calculate final results.
     */
    public function finalizeEvaluation($ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'responsable-projet' && auth()->user()->type !== 'super-admin' && auth()->user()->type !== 'organisation') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

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
                throw new Exception("Auto-evaluation climatique toujours en veuillez patientez", 403);
            }

            // Calculer les résultats finaux
            //$aggregatedScores = $evaluation->getAggregatedScores();

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
            $evaluateurs = $evaluation->evaluateursClimatique()->get()->filter(fn($user) => $user->hasPermissionTo('effectuer-evaluation-climatique-idee-projet'));
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

            $ideeProjet->update([
                'score_climatique' => $finalResults['score_climatique']['score_climatique'],
                'identifiant_bip' => $this->generateIdentifiantBip(),
                'statut' => StatutIdee::IDEE_DE_PROJET  // Marquer comme terminée
            ]);

            $evaluation->update([
                'resultats_evaluation' => $finalResults,
                'valider_le' => now(),
                'statut' => 1  // Marquer comme terminée
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Évaluation finalisée avec succès',
                'data' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation',
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

            if (auth()->user()->type !== 'responsable-projet' && auth()->user()->type !== 'super-admin' && auth()->user()->type !== 'organisation') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

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
            /*$evaluateurs = User::when($ideeProjet->ministere, function ($query) use ($ideeProjet) {
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
                ->where(function ($query) {
                    $query->whereHas('roles', function ($query) {
                        $query->whereHas('permissions', function ($subQuery) {
                            $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                        });
                    })->orWhereHas('role', function ($query) {
                        $query->whereHas('permissions', function ($subQuery) {
                            $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                        });
                    });
                })->get();*/

            $evaluateurs = $evaluation->evaluateursClimatique()->get()->filter(fn($user) => $user->hasPermissionTo('effectuer-evaluation-climatique-idee-projet'));

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
            /*$evaluateurs = User::where('profilable_type', auth()->user()->profilable_type)->where('profilable_id', auth()->user()->profilable_id)
                ->where(function ($query) {
                    $query->whereHas('roles', function ($query) {
                        $query->whereHas('permissions', function ($subQuery) {
                            $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                        });
                    })->orWhereHas('role', function ($query) {
                        $query->whereHas('permissions', function ($subQuery) {
                            $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                        });
                    });
                })->get();*/

            if ($evaluateurs->count() == 0) {
                throw new Exception('Aucun évaluateur trouvé avec la permission "effectuer-evaluation-climatique-idee-projet"', 404);
            }

            $ideeProjet->update([
                'score_climatique' => 0,
                'identifiant_bip' => null,
                'statut' => StatutIdee::BROUILLON  // Marquer comme terminée
            ]);

            $evaluation->update([
                'resultats_evaluation' => [],
                'evaluation' => [],
                'valider_le' => null,
                'statut' => 0  // Marquer comme terminée
            ]);

            $criteresEvaluation->each->update(["est_archiver" => true]);

            DB::commit();

            // Notifier les évaluateurs assignés
            Notification::send($evaluateurs, new EvaluationClimatiqueAssigneeNotification($ideeProjet, $evaluation));

            return response()->json([
                'success' => true,
                'message' => 'Score climatique insatisfaisant, Invitation climatique renvoyer',
                'data' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate completion percentage of evaluation.
     */
    private function calculateCompletionPercentage(Evaluation $evaluation): float
    {
        $totalEvaluateurs = $evaluation->evaluateursClimatique()->count();
        $totalEvaluateurs = $evaluation->evaluateursClimatique()
        ->get()->filter(fn($user) => $user->hasPermissionTo('effectuer-evaluation-climatique-idee-projet'))->count(); // ✅ on vérifie bien si la collection n'est pas vide;

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
    private function calculateFinalResults(object $aggregatedScores): array
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
                'moyenne_evaluateurs' => round($data['moyenne_evaluateurs'] ?? 0, 2),
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

        $critereCount = CategorieCritere::where("slug", "evaluation-preliminaire-multi-projet-impact-climatique")->first()->criteres->count(); //count($results);

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
                    $isAssigned = $evaluation->evaluateursClimatique()
                        ->get()->filter(fn($user) => $user->hasPermissionTo('effectuer-evaluation-climatique-idee-projet'))->isNotEmpty(); // ✅ on vérifie bien si la collection n'est pas vide;
                }
            }

            dd($isAssigned);

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas assigné comme évaluateur pour cette évaluation climatique.',
                ], 403);
            }


            /*$isAssigned = User::when((auth()->user()->profilable_type == Organisation::class || auth()->user()->profilable_type == Dpaf::class) && auth()->user()->profilable?->ministere && $ideeProjet->ministere && (auth()->user()->profilable->ministere?->id == $ideeProjet->ministere->id), function ($query) use ($ideeProjet) {
                $query->where('profilable_type', get_class(auth()->user()->profilable->ministere))
                    ->where('profilable_id', auth()->user()->profilable->ministere->id);/*
                        ->where(function ($query) {
                            $query->whereHas('roles', function ($query) {
                                $query->whereHas('permissions', function ($subQuery) {
                                    $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                                });
                            })->orWhereHas('role', function ($query) {
                                $query->whereHas('permissions', function ($subQuery) {
                                    $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                                });
                            });
                        });
            })->when(auth()->user()->profilable_type == Dgpd::class, function ($query) {
                $query->where(function ($query) {
                        $query->whereHas('roles', function ($query) {
                            $query->whereHas('permissions', function ($subQuery) {
                                $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                            });
                        })->orWhereHas('role', function ($query) {
                            $query->whereHas('permissions', function ($subQuery) {
                                $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                            });
                        });
                    });
            })->where('id', $evaluateurId)
                ->first()->hasPermissionTo("effectuer-evaluation-climatique-idee-projet");

            // Vérifier que l'évaluateur est assigné à cette évaluation
            $isAssigned = EvaluationCritere::where('evaluation_id', $evaluation->id)
                ->autoEvaluation()
                ->active()
                ->where('evaluateur_id', $evaluateurId)
                ->exists();*/

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

                // Mettre à jour la réponse si le critère est éligible
                /*EvaluationCritere::firstOrCreate('evaluation_id', $evaluation->id)
                    ->where('critere_id', $reponse['critere_id'])
                    ->where('evaluateur_id', $evaluateurId)
                    ->update([
                        'evaluation_id' => $evaluation->id,
                        'evaluateur_id' => auth()->id(),
                        'notation_id' => $notation->id,
                        'note' => $notation->valeur, //'Évalué',
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'updated_at' => now()
                    ]);*/
                /*$evaluationCritere = EvaluationCritere::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'evaluateur_id' => $evaluateurId,
                        'categorie_critere_id' => $reponse["categorie_critere_id"],
                        'critere_id' => $reponse['critere_id'],
                        'notation_id' => $notation->id
                    ],
                    [
                        'notation_id' => $notation->id,
                        'note' => $notation->valeur,
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'is_auto_evaluation' => $reponse['is_auto_evaluation'] ?? true,
                        'est_archiver' => false,
                        'updated_at' => now(),
                    ]
                );*/

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

            $evaluation->refresh();

            $evaluationClimatiqueReponses = new Collection([]);

            if (auth()->user()->type == "analyste-dgpd") {

                $outilAMC = CategorieCritere::where("slug", 'grille-analyse-multi-critere')->first();

                if (!$outilAMC) {
                    throw new Exception("Outil AMC introuvable", 404);
                }

                $critereImpactClimatique = $outilAMC->criteres()->whereRaw('LOWER(intitule) LIKE ?', ['%impact climatique%'])/* ->where("intitule", "Impact climatique") */->first();

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

                $score_climatique = $outilAMC->criteres->count() ? ($score_pondere_par_critere->sum('score_pondere') / $outilAMC->criteres->count()) : 0;

                $evaluationAMC = Evaluation::updateOrCreate([
                    'projetable_id' => $ideeProjet->id,
                    'projetable_type' => get_class($ideeProjet),
                    "type_evaluation" => "amc"
                ], [
                    "type_evaluation" => "amc",
                    "statut"  => 0,
                    "date_debut_evaluation" => now(),
                    "evaluation" => [],
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
            } else {
                // Récupérer les réponses de l'évaluateur connecté
                $evaluationClimatiqueReponses = EvaluationCritere::forEvaluation($evaluation->id)
                    ->autoEvaluation()
                    ->active()
                    ->byEvaluateur($evaluateurId)
                    ->with(['critere', 'notation', 'categorieCritere'])
                    ->get();
            }

            DB::commit();

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
                            round(($evaluationClimatiqueReponses->filter->isCompleted()->count() / $evaluationClimatiqueReponses->count()) * 100, 2) : 0
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
     * Obtenir toutes les évaluations de critères d'un évaluateur pour une évaluation.
     */
    public function getEvaluateurCriteres($evaluationId, $evaluateurId = null): JsonResponse
    {
        try {
            $evaluateurId = $evaluateurId ?? auth()->id();

            $evaluation = $this->repository->findOrFail($evaluationId);

            $evaluationCriteres = EvaluationCritere::forEvaluation($evaluationId)
                ->autoEvaluation()
                ->active()
                ->byEvaluateur($evaluateurId)
                ->with(['critere', 'notation', 'categorieCritere', 'evaluateur'])
                ->get();

            // Calculer le score climatique si c'est une évaluation climatique
            $scoreClimatique = null;
            if ($evaluation->type_evaluation === 'climatique') {
                $scoreClimatique = $this->calculateScoreClimatique($evaluationId);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => new EvaluationResource($evaluation),
                    'evaluateur_id' => $evaluateurId,
                    'evaluateur_reponses' => EvaluationCritereResource::collection($evaluationCriteres),
                    'evaluateur_stats' => [
                        'total_criteres' => $evaluationCriteres->count(),
                        'criteres_evalues' => $evaluationCriteres->filter->isCompleted()->count(),
                        'criteres_en_attente' => $evaluationCriteres->filter->isPending()->count(),
                        'taux_completion' => $evaluationCriteres->count() > 0 ?
                            round(($evaluationCriteres->filter->isCompleted()->count() / $evaluationCriteres->count()) * 100, 2) : 0,
                        'score_pondere_evaluateur' => $this->calculateEvaluateurScorePondere($evaluationCriteres)
                    ],
                    'score_global' => $this->calculateScoreGlobal($evaluationId),
                    'score_climatique' => $scoreClimatique
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des critères de l\'évaluateur',
                'error' => $e->getMessage()
            ], 500);
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
            round(($score_total * 100) / $ponderation_totale, 2) : 0;

        return [
            'score_total' => $score_total,
            2,
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

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

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

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
            $evaluateurs = $evaluation->evaluateursClimatique()->get()->filter(fn($user) => $user->hasPermissionTo('effectuer-evaluation-climatique-idee-projet'));

            /* User::when($ideeProjet->ministere, function ($query) use ($ideeProjet) {
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
                }) */
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
                    round(($criteres_completes->count() / $total_criteres) * 100, 2) : 0,
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
                'moyenne_evaluateurs' => round($moyenneEvaluateurs, 2),
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
            round(($scoreTotal * 100) / $ponderationTotale, 2) : 0;

        return [
            //'score_climatique' => $scoreClimatique,
            "score_climatique" => round($criteresMoyennes->avg('score_pondere'), 2),
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
                $ecartType = round(sqrt($variance), 2);
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
                'variance' => round($variance, 2),
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

            if (auth()->user()->type !== 'analyste-dgpd' && auth()->user()->type !== 'super-admin' && auth()->user()->type !== 'dgpd') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            $evaluateurId = auth()->id();
            $reponses = $data['reponses'];

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            /*if (($ideeProjet->statut != StatutIdee::AMC && $ideeProjet->statut != StatutIdee::ANALYSE)) {
                throw new Exception("AMC deja effectuer", 403);
            }*/

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

            /*if ($evaluation->statut == 1) {
                throw new Exception("Evaluation de l'amc deja effectuer", 403);
            }*/

            // Vérifier que l'évaluation climatique existe
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            $evaluation = Evaluation::updateOrCreate([
                'projetable_id' => $ideeProjet->id,
                'projetable_type' => get_class($ideeProjet),
                "type_evaluation" => "amc"
            ], [
                "type_evaluation" => "amc",
                "statut"  => 0,
                "date_debut_evaluation" => now(),
                "evaluation" => [],
                "resultats_evaluation" => []
            ]);

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
                if (str_contains(strtolower($critere->intitule ?? ''), 'impact climatique')) {
                    continue;
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

                EvaluationCritere::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'critere_id' => $critere->id,
                        'categorie_critere_id' => $critere->categorie_critere_id,
                    ],
                    [
                        'notation_id' => $notation->id,
                        'evaluateur_id' => $evaluateurId,
                        'note' => $notation->valeur,
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'is_auto_evaluation' => false,
                        'est_archiver' => false,
                        'updated_at' => now(),
                    ]
                );
            }

            $evaluation->refresh();

            $finalResults = new Collection([]);

            if ($evaluation) {
                $aggregatedScores = $evaluation->getAMCAggregatedScores();
                $finalResults = $this->calculateFinalResults($aggregatedScores);
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
                'resultats_evaluation' => [...$finalResults, ...["score_amc" => collect($finalResults['scores_ponderes_par_critere'])->sum("score_pondere")]],
                'evaluation' => EvaluationCritereResource::collection($evaluateurReponses),
                'valider_le' => now(),
                'statut' => 1  // Marquer comme terminée
            ]);

            $ideeProjet->update([
                'score_amc' => collect($finalResults["scores_ponderes_par_critere"])->avg("score_pondere"),
                'statut' => StatutIdee::VALIDATION  // Marquer comme terminée
            ]);

            $evaluation->refresh();
            DB::commit();

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
                            round(($evaluateurReponses->filter->isCompleted()->count() / $evaluateurReponses->count()) * 100, 2) : 0
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

            $outilClimatique = CategorieCritere::with("criteres")->where("slug", 'evaluation-preliminaire-multi-projet-impact-climatique')->first();

            $score_pondere_par_critere = [];

            $scoreClimatique = 0;
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

            $finalResults = $this->calculateFinalResults($aggregatedScores);

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation_climatique' => [
                        "score_climatique" => round(($score_pondere_par_critere->sum('score_pondere') / $categorie->criteres->count()), 2),
                        "scores_pondere_par_critere" => array_values($score_pondere_par_critere->toArray()),/*  EvaluationCritereResource::collection($critereClimatiqueEvaluer)->resource->toArray()) */
                        "evaluation_effectuer" => EvaluationCritereResource::collection($critereClimatiqueEvaluer)
                    ],
                    'evaluation_amc' => $evaluation ? new EvaluationResource($evaluation) : null,
                    ...$finalResults
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
}
