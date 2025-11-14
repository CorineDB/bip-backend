<?php

namespace App\Console\Commands;

use App\Jobs\ExportAppreciationJob;
use Illuminate\Console\Command;

class TestExportAppreciationJob extends Command
{
    protected $signature = 'test:export-appreciation {projet_id} {--type=note-conceptuelle}';
    protected $description = 'Tester l\'export d\'appréciation via Job (asynchrone)';

    public function handle()
    {
        $projetId = $this->argument('projet_id');
        $type = $this->option('type');

        $this->info("Dispatch du job ExportAppreciationJob...");
        $this->line("  - Projet ID: {$projetId}");
        $this->line("  - Type: {$type}");

        // Dispatcher le job
        ExportAppreciationJob::dispatch($projetId, $type, auth()->id() ?? 1);

        $this->newLine();
        $this->info("✓ Job dispatché avec succès!");
        $this->line("Le job sera exécuté en arrière-plan par le queue worker.");
        $this->line("Utilisez 'php artisan queue:work' pour traiter les jobs en attente.");
        $this->newLine();
        $this->line("Pour suivre l'exécution:");
        $this->line("  - Logs: storage/logs/laravel.log");
        $this->line("  - Queue: php artisan queue:work --verbose");

        return 0;
    }
}
