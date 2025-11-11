<?php

namespace App\Console\Commands;

use App\Jobs\EnvoyerProjetMaturationJob;
use App\Models\Projet;
use App\Services\ExternalApiService;
use Illuminate\Console\Command;

class TestEnvoyerProjetMaturation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:envoyer-projet-maturation {projet_id : L\'ID du projet à envoyer} {--sync : Exécuter de manière synchrone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester l\'envoi d\'un projet mature au système SIGFP';

    /**
     * Execute the console command.
     */
    public function handle(ExternalApiService $externalApiService)
    {
        $projetId = $this->argument('projet_id');
        $sync = $this->option('sync');

        $this->info("🚀 Test d'envoi du projet ID: {$projetId} au système SIGFP");
        $this->newLine();

        // Vérifier que le projet existe
        $projet = Projet::find($projetId);
        if (!$projet) {
            $this->error("❌ Le projet avec l'ID {$projetId} n'existe pas.");
            return Command::FAILURE;
        }

        $this->info("📋 Projet trouvé: {$projet->titre}");
        $this->newLine();

        try {
            if ($sync) {
                // Exécution synchrone pour voir les résultats immédiatement
                $this->info("⏱️  Exécution en mode synchrone...");
                $this->newLine();

                // Préparer le payload
                $this->info("📦 Préparation du payload...");
                $payload = $externalApiService->preparerPayloadProjetMature($projet);

                $this->info("✅ Payload préparé avec succès");
                $this->line("   Transaction ID: " . $payload['transactionId']);
                $this->newLine();

                // Sauvegarder le payload dans un fichier pour inspection
                $payloadFile = storage_path("logs/sigfp_payload_projet_{$projetId}_" . now()->format('Y-m-d_His') . ".json");
                file_put_contents($payloadFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $this->info("💾 Payload sauvegardé dans: {$payloadFile}");
                $this->newLine();

                // Envoyer au système externe
                $this->info("📤 Envoi au système SIGFP...");
                $response = $externalApiService->envoyerProjetMature($payload);

                if ($response === null) {
                    $this->error("❌ Échec de l'envoi au système SIGFP");
                    $this->warn("💡 Vérifiez les logs pour plus de détails");
                    return Command::FAILURE;
                }

                $this->newLine();
                $this->info("✅ Projet envoyé avec succès!");
                $this->line("📥 Réponse du serveur:");
                $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                return Command::SUCCESS;

            } else {
                // Exécution asynchrone (via la queue)
                $this->info("📨 Dispatch du job dans la queue...");
                EnvoyerProjetMaturationJob::dispatch($projetId);

                $this->newLine();
                $this->info("✅ Job dispatché avec succès!");
                $this->warn("💡 Vérifiez les logs et la queue pour suivre l'exécution");
                $this->line("   Commande: php artisan queue:work");

                return Command::SUCCESS;
            }

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("❌ Erreur lors de l'envoi:");
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn("📋 Stack trace:");
            $this->line($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
