<?php

namespace App\Jobs;

use App\Models\IdeeProjet;
use App\Services\NoteConceptuelleExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExportNoteConceptuelleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    protected $ideeProjetId;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $ideeProjetId, ?int $userId = null)
    {
        $this->ideeProjetId = $ideeProjetId;
        $this->userId = $userId ?? auth()->id();
    }

    /**
     * Execute the job.
     */
    public function handle(NoteConceptuelleExportService $exportService): void
    {
        try {
            Log::info("Début export note conceptuelle", [
                'idee_projet_id' => $this->ideeProjetId,
                'user_id' => $this->userId
            ]);

            $ideeProjet = IdeeProjet::findOrFail($this->ideeProjetId);

            $projet = $ideeProjet->projet;

            if (!$projet) {
                throw new \Exception("L'idée de projet {$this->ideeProjetId} n'a pas de projet associé");
            }

            $result = $exportService->exportNoteConceptuelle($projet);

            Log::info("Export note conceptuelle réussi", [
                'idee_projet_id' => $this->ideeProjetId,
                'projet_id' => $projet->id,
                'file_name' => $result['file_name'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error("Échec export note conceptuelle", [
                'idee_projet_id' => $this->ideeProjetId,
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
        Log::error("Export note conceptuelle échoué définitivement", [
            'idee_projet_id' => $this->ideeProjetId,
            'error' => $exception->getMessage()
        ]);
    }
}
