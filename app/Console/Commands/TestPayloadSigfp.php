<?php

namespace App\Console\Commands;

use App\Models\Projet;
use App\Services\ExternalApiService;
use Illuminate\Console\Command;

class TestPayloadSigfp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payload-sigfp {projet_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Affiche le payload formaté pour SIGFP sans l\'envoyer';

    /**
     * Execute the console command.
     */
    public function handle(ExternalApiService $externalApiService)
    {
        $projetId = $this->argument('projet_id');

        $this->info("Récupération du projet ID: {$projetId}");

        try {
            // Récupérer le projet
            $projet = Projet::findOrFail($projetId);

            $this->info("Projet trouvé: {$projet->titre}");

            $this->newLine();

            // Préparer le payload
            $payload = $externalApiService->preparerPayloadProjetMature(
                $projet,
                ['commentaire' => 'Test de formatage']
            );

            // Afficher le payload formaté
            $this->line("========================================");
            $this->line("PAYLOAD SIGFP");
            $this->line("========================================");
            $this->newLine();

            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $this->newLine();
            $this->line("========================================");

            // Afficher les statistiques
            $this->newLine();
            $this->info("Statistiques:");
            $this->table(
                ['Propriété', 'Valeur'],
                [
                    ['Transaction ID', $payload['transactionId']],
                    ['Type', $payload['transactionType']],
                    ['Système source', $payload['systemeSource']],
                    ['Système destination', $payload['systemeDestination']],
                    ['Version schema', $payload['versionSchema']],
                    ['Nombre de projets', count($payload['data'])],
                    ['Taille JSON (bytes)', strlen(json_encode($payload))],
                ]
            );

            // Sauvegarder dans un fichier
            $filename = storage_path("logs/sigfp_payload_projet_{$projetId}_" . now()->format('Y-m-d_His') . ".json");
            file_put_contents($filename, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $this->newLine();
            $this->info("✓ Payload sauvegardé dans: {$filename}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
