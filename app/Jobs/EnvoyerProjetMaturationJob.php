<?php

namespace App\Jobs;

use App\Models\Projet;
use App\Services\ExternalApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ErreurEnvoiProjetNotification;
use Exception;

class EnvoyerProjetMaturationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Le nombre de fois que le job peut être tenté.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Le nombre de secondes avant de réessayer après un échec.
     *
     * @var array
     */
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    /**
     * Le nombre de secondes avant que le job expire.
     *
     * @var int
     */
    public $timeout = 120;

    protected int $projetId;
    protected array $additionalData;
    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $projetId,
        array $additionalData = [],
        ?int $userId = null
    ) {
        $this->projetId = $projetId;
        $this->additionalData = $additionalData;
        $this->userId = $userId ?? auth()->id();
    }

    /**
     * Execute the job.
     */
    public function handle(ExternalApiService $externalApiService): void
    {
        try {
            // Récupérer le projet
            $projet = Projet::findOrFail($this->projetId);

            Log::info('Début de l\'envoi du projet mature au système externe', [
                'projet_id' => $this->projetId,
                'attempt' => $this->attempts()
            ]);

            // Préparer les données au format attendu
            $payload = $externalApiService->preparerPayloadProjetMature(
                $projet,
                $this->additionalData
            );

            // Envoyer les données au système externe
            $response = $externalApiService->envoyerProjetMature($payload);

            if ($response === null) {
                throw new Exception('Échec de l\'envoi au système externe SIGFP');
            }

            Log::info('Projet mature envoyé avec succès au système externe', [
                'projet_id' => $this->projetId,
                'response' => $response
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'envoi du projet mature au système externe', [
                'projet_id' => $this->projetId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Si c'est la dernière tentative, on relance l'exception pour déclencher failed()
            if ($this->attempts() >= $this->tries) {
                throw $e;
            }

            // Sinon on relance pour retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Échec définitif de l\'envoi du projet mature après ' . $this->tries . ' tentatives', [
            'projet_id' => $this->projetId,
            'error' => $exception->getMessage()
        ]);

        // Envoyer une notification par email aux administrateurs
        try {
            $projet = Projet::find($this->projetId);
            $user = \App\Models\User::find($this->userId);

            if ($user) {
                $user->notify(new ErreurEnvoiProjetNotification(
                    $projet,
                    $exception->getMessage(),
                    $this->tries
                ));
            }

            // Notifier également les administrateurs système
            $admins = \App\Models\User::where('type', 'admin')
                ->orWhereHas('roles', function ($query) {
                    $query->where('name', 'Administrateur');
                })
                ->get();

            Notification::send($admins, new ErreurEnvoiProjetNotification(
                $projet,
                $exception->getMessage(),
                $this->tries
            ));

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification d\'échec', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
