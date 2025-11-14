<?php

namespace App\Jobs;

use App\Models\IdeeProjet;
use App\Services\EvaluationExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExportEvaluationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    protected $ideeProjetId;
    protected $type; // 'climatique' ou 'pertinence'
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $ideeProjetId, string $type, ?int $userId = null)
    {
        $this->ideeProjetId = $ideeProjetId;
        $this->type = $type;
        $this->userId = $userId ?? auth()->id();
    }

    /**
     * Execute the job.
     */
    public function handle(EvaluationExportService $exportService): void
    {
        try {
            Log::info("Début export évaluation", [
                'idee_projet_id' => $this->ideeProjetId,
                'type' => $this->type,
                'user_id' => $this->userId
            ]);

            $ideeProjet = IdeeProjet::findOrFail($this->ideeProjetId);

            $result = $exportService->export($ideeProjet, $this->type);

            Log::info("Export évaluation réussi", [
                'idee_projet_id' => $this->ideeProjetId,
                'type' => $this->type,
                'file_name' => $result['file_name'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error("Échec export évaluation", [
                'idee_projet_id' => $this->ideeProjetId,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Gérer l'échec du job après tous les essais
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Export évaluation échoué définitivement", [
            'idee_projet_id' => $this->ideeProjetId,
            'type' => $this->type,
            'error' => $exception->getMessage()
        ]);
    }
}
