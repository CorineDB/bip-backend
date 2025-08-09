<?php

namespace App\Listeners;

use App\Events\IdeeProjetCree;
use App\Models\User;
use App\Models\Evaluation;
use App\Models\EvaluationCritere;
use App\Models\Critere;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EvaluationClimatiqueAssigneeNotification;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\Schema;

class CreerEvaluationClimatique implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    //public $connection = 'database';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    //public $queue = 'listeners';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    //public $delay = 60;

    /**
     * The number of seconds to wait before retrying the queued listener.
     *
     * @var int
     */
    //public $backoff = 3;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    //public $tries = 25;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    //public $maxExceptions = 3;

    /**
     * The number of seconds the listener can run before timing out.
     *
     * @var int
     */
    //public $timeout = 120;

    /**
     * Indicate if the listener should be marked as failed on timeout.
     *
     * @var bool
     */
    //public $failOnTimeout = true;

    /**
     * Handle the event.
     */
    public function handle(IdeeProjetCree $event): void
    {
        try {
            DB::beginTransaction();

            $ideeProjet = $event->ideeProjet;

            // Créer l'évaluation climatique
            $evaluation = Evaluation::create([
                'type_evaluation' => 'climatique',
                'updated_at' => now(),
                'created_at' => now(),
                'date_debut_evaluation' => now(),
                'projetable_type' => get_class($ideeProjet),
                'projetable_id' => $ideeProjet->id,
                'evaluateur_id' => $ideeProjet->responsable->id,
                'commentaire' => " ",
                'resultats_evaluation' => [],
                'evaluation' => [],
            ]);

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
            //where('profilable_type', $ideeProjet->responsable->profilable_type)->whereNotNull('profilable_id', $ideeProjet->responsable->profilable_id)

            /*$evaluateurs = User::when($ideeProjet->ministere, function ($query) use ($ideeProjet) {
                $query->where(function ($q) use ($ideeProjet) {
                    $q->where('profilable_type', get_class($ideeProjet->ministere))
                      ->where('profilable_id', $ideeProjet->ministere->id);
                });
            })
                ->when($ideeProjet->responsable?->profilable->ministere, function ($query) use ($ideeProjet) {
                    $query
                        ->where('profilable_type', function ($query) use ($ideeProjet) {
                            $ministere = $ideeProjet->responsable->profilable->ministere;
                            $query->where('profilable_type', get_class($ministere))->where('profilable_id', $ministere->id);
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

            $evaluateurs = User::when($ideeProjet->ministere, function ($query) use ($ideeProjet) {
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
                })->get();


            Log::info("Evaluateurs : " . json_encode($evaluateurs));

            if ($evaluateurs->count() == 0) {
                Log::warning('Aucun évaluateur trouvé avec la permission "effectuer-evaluation-climatique-idee-projet"');
                //DB::rollBack();
                return;
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

            Log::info("Criteres : " . json_encode($criteres));

            Schema::disableForeignKeyConstraints();

            // Assigner chaque évaluateur à tous les critères
            foreach ($evaluateurs as $evaluateur) {
                foreach ($criteres as $critere) {
                    EvaluationCritere::updateOrCreate([
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
            Schema::enableForeignKeyConstraints();

            DB::commit();

            // Notifier les évaluateurs assignés
            Notification::send($evaluateurs, new EvaluationClimatiqueAssigneeNotification($ideeProjet, $evaluation));

            Log::info('Évaluation climatique créée avec succès pour l\'idée de projet: ' . $ideeProjet->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'évaluation climatique: ' . $e->getMessage());
        }
    }


    /**
     * Get the middleware the listener should pass through.
     *
     * @return array<int, object>
     */
    /*public function middleware(IdeeProjetCree $event): array
    {
        return []; //[new RateLimited];
    }*/

    /**
     * Handle a job failure.
     */
    /*public function failed(IdeeProjetCree $event, Throwable $exception): void
    {
        // ...
    }*/

    /**
     * Determine the time at which the listener should timeout.
     */
    /*public function retryUntil(): DateTime
    {
        return now()->addMinutes(5);
    }*/

    /**
     * Calculate the number of seconds to wait before retrying the queued listener.
     *
     * @return list<int>
     */
    /*public function backoff(IdeeProjetCree $event): array
    {
        return [1, 5, 10];
    }*/
}
