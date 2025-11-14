<?php

namespace App\Jobs;

use App\Models\Projet;
use App\Services\AppreciationNoteConceptuelleExportService;
use App\Services\AppreciationTdrFaisabiliteExportService;
use App\Services\AppreciationTdrPrefaisabiliteExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ExportAppreciationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Le nombre de fois que le job peut être tenté
     */
    public $tries = 3;

    /**
     * Le nombre de secondes avant timeout
     */
    public $timeout = 300; // 5 minutes

    /**
     * @var int
     */
    protected $projetId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int|null
     */
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $projetId, string $type, ?int $userId = null)
    {
        $this->projetId = $projetId;
        $this->type = $type;
        $this->userId = $userId ?? auth()->id();
    }

    /**
     * Execute the job.
     */
    public function handle(
        AppreciationNoteConceptuelleExportService $noteConceptuelleService,
        AppreciationTdrFaisabiliteExportService $tdrFaisabiliteService,
        AppreciationTdrPrefaisabiliteExportService $tdrPrefaisabiliteService
    ): void
    {
        try {
            Log::info("Début export appréciation", [
                'projet_id' => $this->projetId,
                'type' => $this->type,
                'user_id' => $this->userId
            ]);

            $projet = Projet::findOrFail($this->projetId);

            $result = null;
            switch ($this->type) {
                case 'note-conceptuelle':
                    if (!$projet->noteConceptuelle) {
                        throw new \Exception("Le projet n'a pas de note conceptuelle");
                    }
                    $result = $noteConceptuelleService->export($projet);
                    break;

                case 'tdr-faisabilite':
                    if (!$projet->tdrFaisabilite->first()) {
                        throw new \Exception("Le projet n'a pas de TDR de faisabilité");
                    }
                    $result = $tdrFaisabiliteService->export($projet);
                    break;

                case 'tdr-prefaisabilite':
                    if (!$projet->tdrPrefaisabilite->first()) {
                        throw new \Exception("Le projet n'a pas de TDR de préfaisabilité");
                    }
                    $result = $tdrPrefaisabiliteService->export($projet);
                    break;

                default:
                    throw new \Exception("Type d'appréciation non valide: {$this->type}");
            }

            Log::info("Export appréciation réussi", [
                'projet_id' => $this->projetId,
                'type' => $this->type,
                'file_name' => $result['file_name'] ?? null,
                'size' => $result['size_formatted'] ?? null
            ]);

            // TODO: Notifier l'utilisateur du succès de l'export
            // Notification::send($user, new ExportAppreciationSuccessNotification($result));

        } catch (\Exception $e) {
            Log::error("Échec export appréciation", [
                'projet_id' => $this->projetId,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Relancer l'exception pour que Laravel gère les retry
            throw $e;
        }
    }

    /**
     * Gérer l'échec du job après tous les essais
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Export appréciation échoué définitivement", [
            'projet_id' => $this->projetId,
            'type' => $this->type,
            'error' => $exception->getMessage()
        ]);

        // TODO: Notifier l'utilisateur de l'échec
        // Notification::send($user, new ExportAppreciationFailedNotification($exception));
    }
}
