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

            // Charger le projet avec ses relations
            $ideeProjet = IdeeProjet::with([
                'ministere',
                'evaluationPertinence',
                'evaluationAMC'
            ])->findOrFail($this->ideeProjetId);

            // Récupérer l'évaluation appropriée selon le type
            $evaluation = match($this->type) {
                'pertinence' => $ideeProjet->evaluationPertinence->first(),
                'climatique' => $ideeProjet->evaluationAMC->first(),
                'amc' => $ideeProjet->evaluationAMC->first(),
                default => null
            };

            if (!$evaluation) {
                throw new \Exception("Aucune évaluation de type '{$this->type}' trouvée pour le projet {$this->ideeProjetId}");
            }

            // Appeler la méthode appropriée selon le type
            $storedPath = match($this->type) {
                'pertinence' => $exportService->exportPertinenceToExcel($evaluation),
                'climatique' => $exportService->exportClimatiqueToExcel($evaluation),
                default => throw new \Exception("Type d'évaluation non supporté: {$this->type}")
            };

            Log::info("Export évaluation réussi", [
                'idee_projet_id' => $this->ideeProjetId,
                'type' => $this->type,
                'identifiant_bip' => $ideeProjet->identifiant_bip,
                'stored_path' => $storedPath
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
