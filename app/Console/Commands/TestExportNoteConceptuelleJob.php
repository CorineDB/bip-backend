<?php

namespace App\Console\Commands;

use App\Jobs\ExportNoteConceptuelleJob;
use App\Models\IdeeProjet;
use Illuminate\Console\Command;

class TestExportNoteConceptuelleJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:export-note {idee_projet_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester l\'export de note conceptuelle via le système de jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ideeProjetId = $this->argument('idee_projet_id');

        $this->info("Vérification de l'idée de projet {$ideeProjetId}...");

        $ideeProjet = IdeeProjet::find($ideeProjetId);

        if (!$ideeProjet) {
            $this->error("Idée de projet {$ideeProjetId} introuvable");
            return 1;
        }

        $this->info("Idée de projet trouvée: {$ideeProjet->titre_projet}");
        $this->newLine();

        $this->info("Dispatching du job ExportNoteConceptuelleJob...");

        ExportNoteConceptuelleJob::dispatch($ideeProjetId, auth()->id() ?? 1);

        $this->newLine();
        $this->info("✓ Job dispatché avec succès!");
        $this->newLine();
        $this->line("Pour traiter le job, exécutez dans un autre terminal:");
        $this->line("  php artisan queue:work");
        $this->newLine();
        $this->line("Pour voir les logs:");
        $this->line("  tail -f storage/logs/laravel.log");

        return 0;
    }
}
