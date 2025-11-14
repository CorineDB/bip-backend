<?php

namespace App\Console\Commands;

use App\Models\Projet;
use App\Services\AppreciationNoteConceptuelleExportService;
use App\Services\AppreciationTdrFaisabiliteExportService;
use App\Services\AppreciationTdrPrefaisabiliteExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportAppreciationCommand extends Command
{
    protected $signature = 'appreciation:export {projet_id} {--type=note-conceptuelle : Le type d\'appréciation à exporter (note-conceptuelle, tdr-faisabilite, tdr-prefaisabilite)}';
    protected $description = 'Exporter l\'appréciation pour un projet';

    public function handle(
        AppreciationNoteConceptuelleExportService $noteConceptuelleService,
        AppreciationTdrFaisabiliteExportService $tdrFaisabiliteService,
        AppreciationTdrPrefaisabiliteExportService $tdrPrefaisabiliteService
    ) {
        $projetId = $this->argument('projet_id');
        $type = $this->option('type');

        $this->info("Recherche du projet {$projetId}...");

        $projet = Projet::find($projetId);

        if (!$projet) {
            $this->error("Projet {$projetId} introuvable");
            return 1;
        }

        $this->info("Exportation de l'appréciation de type '{$type}' pour le projet {$projet->identifiant_bip}...");

        try {
            $result = null;
            switch ($type) {
                case 'note-conceptuelle':
                    if (!$projet->noteConceptuelle) {
                        $this->error("Le projet n'a pas de note conceptuelle");
                        return 1;
                    }
                    $result = $noteConceptuelleService->export($projet);
                    break;
                case 'tdr-faisabilite':
                    if (!$projet->tdrFaisabilite->first()) {
                        $this->error("Le projet n'a pas de TDR de faisabilité");
                        return 1;
                    }
                    $result = $tdrFaisabiliteService->export($projet);
                    break;
                case 'tdr-prefaisabilite':
                    if (!$projet->tdrPrefaisabilite->first()) {
                        $this->error("Le projet n'a pas de TDR de préfaisabilité");
                        return 1;
                    }
                    $result = $tdrPrefaisabiliteService->export($projet);
                    break;
                default:
                    $this->error("Type d'appréciation non valide: {$type}");
                    return 1;
            }

            $this->newLine();
            $this->info("✓ Export réussi!");
            $this->line("Chemin de stockage: " . Storage::path($result['storage_path']));

            $this->newLine();
            $this->line("Informations du fichier:");
            $this->line("  - Nom du fichier: {$result['file_name']}");
            $this->line("  - Taille: {$result['size_formatted']}");
            $this->line("  - MD5: {$result['md5']}");

            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'export: " . $e->getMessage());
            //$this->error($e->getTraceAsString());
            return 1;
        }
    }
}
