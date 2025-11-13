<?php

namespace App\Console\Commands;

use App\Models\IdeeProjet;
use App\Services\NoteConceptuelleExportService;
use Illuminate\Console\Command;

class ExportNoteConceptuelleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'note-conceptuelle:export {ideeProjetId : ID de l\'idée de projet}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporter la note conceptuelle d\'un projet vers DOCX';

    /**
     * Execute the console command.
     */
    public function handle(NoteConceptuelleExportService $exportService)
    {
        $ideeProjetId = $this->argument('ideeProjetId');

        $this->info("Recherche de l'idée de projet {$ideeProjetId}...");

        $ideeProjet = IdeeProjet::find($ideeProjetId);

        if (!$ideeProjet) {
            $this->error("Idée de projet {$ideeProjetId} introuvable");
            return 1;
        }

        if (!$ideeProjet->projet) {
            $this->error("Aucun projet associé à cette idée de projet");
            return 1;
        }

        $projet = $ideeProjet->projet;
        $identifiantBip = $projet->identifiant_bip ?? 'PROJET-' . $projet->id;

        $this->info("Exportation de la note conceptuelle pour le projet {$identifiantBip}...");

        try {
            $result = $exportService->exportNoteConceptuelle($projet);

            $this->newLine();
            $this->info('✓ Export réussi!');
            $this->line("Chemin de stockage: {$result['storage_path']}");

            $this->newLine();
            $this->info('Informations du fichier:');
            $this->line("  - Nom original: {$result['original_name']}");
            $this->line("  - Taille: {$result['size_formatted']}");
            $this->line("  - MD5: {$result['md5']}");

            return 0;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ Erreur lors de l\'export:');
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }
    }
}
