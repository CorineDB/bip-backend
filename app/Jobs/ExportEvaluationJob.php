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
            Log::info("📤 [ExportEvaluationJob] Début export évaluation", [
                'idee_projet_id' => $this->ideeProjetId,
                'type' => $this->type,
                'user_id' => $this->userId,
                'attempt' => $this->attempts()
            ]);

            // Charger le projet avec ses relations
            Log::info("📋 [ExportEvaluationJob] Chargement du projet", [
                'idee_projet_id' => $this->ideeProjetId
            ]);

            $ideeProjet = IdeeProjet::with([
                'ministere',
                'evaluationPertinence',
                'evaluationAMC'
            ])->findOrFail($this->ideeProjetId);

            Log::info("✅ [ExportEvaluationJob] Projet chargé", [
                'idee_projet_id' => $this->ideeProjetId,
                'identifiant_bip' => $ideeProjet->identifiant_bip,
                'titre' => $ideeProjet->titre_projet
            ]);

            // Récupérer l'évaluation appropriée selon le type
            Log::info("🔍 [ExportEvaluationJob] Recherche de l'évaluation", [
                'type' => $this->type
            ]);

            $evaluation = match($this->type) {
                'pertinence' => $ideeProjet->evaluationPertinence->first(),
                'climatique' => $ideeProjet->evaluationAMC->first(),
                'amc' => $ideeProjet->evaluationAMC->first(),
                default => null
            };

            if (!$evaluation) {
                Log::warning("⚠️ [ExportEvaluationJob] Aucune évaluation trouvée", [
                    'idee_projet_id' => $this->ideeProjetId,
                    'type' => $this->type
                ]);
                throw new \Exception("Aucune évaluation de type '{$this->type}' trouvée pour le projet {$this->ideeProjetId}");
            }

            Log::info("✅ [ExportEvaluationJob] Évaluation trouvée", [
                'evaluation_id' => $evaluation->id,
                'type' => $this->type,
                'statut' => $evaluation->statut
            ]);

            // Appeler la méthode appropriée selon le type
            $methodName = match($this->type) {
                'pertinence' => 'exportPertinenceToExcel',
                'climatique', 'amc' => 'exportClimatiqueToExcel',
                default => throw new \Exception("Type d'évaluation non supporté: {$this->type}")
            };

            Log::info("📝 [ExportEvaluationJob] Appel du service d'export", [
                'type' => $this->type,
                'method' => $methodName
            ]);

            $storedPath = match($this->type) {
                'pertinence' => $exportService->exportPertinenceToExcel($evaluation),
                'climatique', 'amc' => $exportService->exportClimatiqueToExcel($evaluation),
                default => throw new \Exception("Type d'évaluation non supporté: {$this->type}")
            };

            Log::info("✅ [ExportEvaluationJob] Export évaluation réussi", [
                'idee_projet_id' => $this->ideeProjetId,
                'type' => $this->type,
                'identifiant_bip' => $ideeProjet->identifiant_bip,
                'stored_path' => $storedPath,
                'attempt' => $this->attempts()
            ]);

        } catch (\Exception $e) {
            Log::error("❌ [ExportEvaluationJob] Échec export évaluation", [
                'idee_projet_id' => $this->ideeProjetId,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries
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
