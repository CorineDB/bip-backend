<?php

namespace App\Console\Commands;

use App\Jobs\ExportEvaluationJob;
use App\Models\IdeeProjet;
use Illuminate\Console\Command;

class TestExportEvaluationJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:export-evaluation {idee_projet_id} {--type=climatique : Type d\'évaluation (climatique ou pertinence)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester l\'export d\'évaluation via le système de jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ideeProjetId = $this->argument('idee_projet_id');
        $type = $this->option('type');

        if (!in_array($type, ['climatique', 'pertinence'])) {
            $this->error("Type d'évaluation invalide. Valeurs acceptées: climatique, pertinence");
            return 1;
        }

        $this->info("Vérification de l'idée de projet {$ideeProjetId}...");

        $ideeProjet = IdeeProjet::find($ideeProjetId);

        if (!$ideeProjet) {
            $this->error("Idée de projet {$ideeProjetId} introuvable");
            return 1;
        }

        $this->info("Idée de projet trouvée: {$ideeProjet->titre_projet}");
        $this->info("Type d'évaluation: {$type}");
        $this->newLine();

        $this->info("Dispatching du job ExportEvaluationJob...");

        ExportEvaluationJob::dispatch($ideeProjetId, $type, auth()->id() ?? 1);

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
