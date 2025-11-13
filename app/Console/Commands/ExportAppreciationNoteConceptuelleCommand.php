<?php

namespace App\Console\Commands;

use App\Models\Projet;
use App\Services\AppreciationNoteConceptuelleExportService;
use Illuminate\Console\Command;

class ExportAppreciationNoteConceptuelleCommand extends Command
{
    protected $signature = 'appreciation:export {projet_id}';
    protected $description = 'Exporter l\'appréciation de la note conceptuelle pour un projet';

    public function handle(AppreciationNoteConceptuelleExportService $exportService)
    {
        $projetId = $this->argument('projet_id');

        $this->info("Recherche du projet {$projetId}...");

        $projet = Projet::find($projetId);

        if (!$projet) {
            $this->error("Projet {$projetId} introuvable");
            return 1;
        }

        if (!$projet->noteConceptuelle) {
            $this->error("Le projet n'a pas de note conceptuelle");
            return 1;
        }

        $this->info("Exportation de l'appréciation pour le projet {$projet->identifiant_bip}...");

        try {
            $result = $exportService->export($projet);

            $this->newLine();
            $this->info("✓ Export réussi!");
            $this->line("Chemin de stockage: {$result['path']}");

            $this->newLine();
            $this->line("Informations du fichier:");
            $this->line("  - Nom original: {$result['fichier']->nom_original}");
            $this->line("  - Taille: " . number_format($result['size'] / 1024 / 1024, 2) . " MB");
            $this->line("  - MD5: {$result['md5']}");

            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'export: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
