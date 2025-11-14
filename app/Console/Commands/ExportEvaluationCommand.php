<?php

namespace App\Console\Commands;

use App\Models\Evaluation;
use App\Models\IdeeProjet;
use App\Services\EvaluationExportService;
use Illuminate\Console\Command;

class ExportEvaluationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluation:export {project_id} {--type=pertinence}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporter une évaluation au format Excel depuis l\'ID du projet et le type d\'évaluation';

    protected EvaluationExportService $exportService;

    public function __construct(EvaluationExportService $exportService)
    {
        parent::__construct();
        $this->exportService = $exportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectId = $this->argument('project_id');
        $type = $this->option('type');

        // Charger le projet avec ses relations
        $project = IdeeProjet::with([
            'ministere',
            'evaluationPertinence',
            'evaluationAMC'
        ])->find($projectId);

        if (!$project) {
            $this->error("Projet non trouvé avec l'ID : {$projectId}");
            return 1;
        }

        // Récupérer l'évaluation appropriée selon le type
        $evaluation = match($type) {
            'pertinence' => $project->evaluationPertinence->first(),
            'climatique' => $project->evaluationAMC->first(),
            'amc' => $project->evaluationAMC->first(),
            default => null
        };

        if (!$evaluation) {
            $this->error("Aucune évaluation de type '{$type}' trouvée pour le projet {$projectId}");
            return 1;
        }

        $this->info("Exportation de l'évaluation {$type} pour le projet {$projectId} (BIP: {$project->identifiant_bip})...");

        try {
            switch ($type) {
                case 'pertinence':
                    $storedPath = $this->exportService->exportPertinenceToExcel($evaluation);
                    break;

                case 'climatique':
                    $storedPath = $this->exportService->exportClimatiqueToExcel($evaluation);
                    break;

                case 'amc':
                    $this->error("Export AMC pas encore implémenté");
                    return 1;

                default:
                    $this->error("Type d'évaluation non supporté: {$type}");
                    return 1;
            }

            $this->info("✓ Export réussi!");
            $this->info("Chemin de stockage: {$storedPath}");

            // Afficher les infos du fichier créé
            $fichier = $project->fichiers()
                ->where('categorie', "evaluation_{$type}")
                ->latest()
                ->first();

            if ($fichier) {
                $this->info("\nInformations du fichier:");
                $this->line("  - Nom original: {$fichier->nom_original}");
                $this->line("  - Taille: " . number_format($fichier->taille / 1024, 2) . " KB");
                $this->line("  - MD5: {$fichier->hash_md5}");
                $this->line("  - Dossier public: " . ($fichier->metadata['dossier_public'] ?? 'N/A'));
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'export: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
