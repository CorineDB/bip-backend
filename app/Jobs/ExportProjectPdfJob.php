<?php

namespace App\Jobs;

use App\Models\IdeeProjet;
use App\Services\ProjectExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExportProjectPdfJob implements ShouldQueue
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
    public function handle(ProjectExportService $exportService): void
    {
        try {
            Log::info("Début export PDF projet", [
                'idee_projet_id' => $this->ideeProjetId,
                'user_id' => $this->userId
            ]);

            $ideeProjet = IdeeProjet::findOrFail($this->ideeProjetId);

            // La méthode exportToPdf génère le fichier et le sauvegarde dans la base de données
            // Elle retourne une Response (download) mais nous l'ignorons dans le contexte du job
            $exportService->exportToPdf($ideeProjet);

            Log::info("Export PDF projet réussi", [
                'idee_projet_id' => $this->ideeProjetId,
                'identifiant_bip' => $ideeProjet->identifiant_bip
            ]);

        } catch (\Exception $e) {
            Log::error("Échec export PDF projet", [
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
        Log::error("Export PDF projet échoué définitivement", [
            'idee_projet_id' => $this->ideeProjetId,
            'error' => $exception->getMessage()
        ]);
    }
}
