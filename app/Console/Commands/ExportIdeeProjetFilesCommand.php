<?php

namespace App\Console\Commands;

use App\Jobs\ExportEvaluationJob;
use App\Jobs\ExportProjectPdfJob;
use App\Models\IdeeProjet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExportIdeeProjetFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idees:export-files
                            {--limit= : Limiter le nombre d\'idées à traiter}
                            {--ids= : IDs spécifiques séparés par des virgules}
                            {--statut= : Filtrer par statut (ex: analyse,validation,note_conceptuel)}
                            {--dry-run : Mode test sans dispatcher les jobs}
                            {--types=* : Types d\'exports à effectuer (fiche,pertinence,climatique,amc). Par défaut: tous}
                            {--force : Forcer l\'export même si les fichiers existent déjà}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporter les fichiers (fiche, pertinence, climatique, AMC) pour les idées de projet existantes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Démarrage de l\'export des fichiers pour les idées de projet');
        $this->newLine();

        // Récupérer les options
        $limit = $this->option('limit');
        $ids = $this->option('ids');
        $statut = $this->option('statut');
        $dryRun = $this->option('dry-run');
        $types = $this->option('types');
        $force = $this->option('force');

        // Si aucun type spécifié, exporter tous les types
        if (empty($types)) {
            $types = ['fiche', 'pertinence', 'climatique', 'amc'];
        }

        $this->info('📋 Configuration:');
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Limit', $limit ?? 'Aucune'],
                ['IDs spécifiques', $ids ?? 'Non'],
                ['Statut', $statut ?? 'Tous'],
                ['Mode dry-run', $dryRun ? 'Oui' : 'Non'],
                ['Types d\'export', implode(', ', $types)],
                ['Forcer', $force ? 'Oui' : 'Non'],
            ]
        );
        $this->newLine();

        // Construire la requête
        $query = IdeeProjet::query();

        // Filtrer par IDs si spécifié
        if ($ids) {
            $idArray = array_map('trim', explode(',', $ids));
            $query->whereIn('id', $idArray);
            $this->info("🔍 Filtrage par IDs: " . implode(', ', $idArray));
        }

        // Filtrer par statut si spécifié
        if ($statut) {
            $query->where('statut', $statut);
            $this->info("🔍 Filtrage par statut: {$statut}");
        }

        // Appliquer la limite si spécifiée
        if ($limit) {
            $query->limit((int) $limit);
        }

        // Exécuter la requête
        $idees = $query->with(['evaluationPertinence', 'evaluationAMC'])->get();

        $this->info("📊 Nombre d'idées de projet trouvées: " . $idees->count());
        $this->newLine();

        if ($idees->isEmpty()) {
            $this->warn('⚠️ Aucune idée de projet trouvée avec ces critères.');
            return 0;
        }

        if ($dryRun) {
            $this->warn('⚠️ MODE DRY-RUN: Aucun job ne sera dispatché');
            $this->newLine();
        }

        // Confirmer avant de continuer
        if (!$dryRun && !$this->confirm("Voulez-vous dispatcher les jobs d'export pour {$idees->count()} idée(s) de projet?", true)) {
            $this->info('❌ Opération annulée');
            return 0;
        }

        $this->newLine();
        $progressBar = $this->output->createProgressBar($idees->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        $stats = [
            'total' => $idees->count(),
            'fiche' => 0,
            'pertinence' => 0,
            'climatique' => 0,
            'amc' => 0,
            'errors' => 0,
        ];

        foreach ($idees as $idee) {
            $progressBar->setMessage("Traitement de l'idée #{$idee->id} - {$idee->identifiant_bip}");

            try {
                // Export de la fiche idée projet (PDF)
                if (in_array('fiche', $types)) {
                    if (!$dryRun) {
                        ExportProjectPdfJob::dispatch($idee->id, auth()->id() ?? 1);
                        Log::info("📄 [ExportIdeeProjetFiles] Job fiche dispatché", [
                            'idee_projet_id' => $idee->id,
                            'identifiant_bip' => $idee->identifiant_bip
                        ]);
                    }
                    $stats['fiche']++;
                }

                // Export évaluation de pertinence
                if (in_array('pertinence', $types)) {
                    $evalPertinence = $idee->evaluationPertinence->first();
                    if ($evalPertinence && $evalPertinence->statut == 1) {
                        if (!$dryRun) {
                            ExportEvaluationJob::dispatch($idee->id, 'pertinence', auth()->id() ?? 1);
                            Log::info("📊 [ExportIdeeProjetFiles] Job pertinence dispatché", [
                                'idee_projet_id' => $idee->id,
                                'evaluation_id' => $evalPertinence->id
                            ]);
                        }
                        $stats['pertinence']++;
                    }
                }

                // Export évaluation climatique
                if (in_array('climatique', $types)) {
                    $evalAMC = $idee->evaluationAMC->first();
                    if ($evalAMC && $evalAMC->statut == 1) {
                        if (!$dryRun) {
                            ExportEvaluationJob::dispatch($idee->id, 'climatique', auth()->id() ?? 1);
                            Log::info("🌍 [ExportIdeeProjetFiles] Job climatique dispatché", [
                                'idee_projet_id' => $idee->id,
                                'evaluation_id' => $evalAMC->id
                            ]);
                        }
                        $stats['climatique']++;
                    }
                }

                // Export évaluation AMC
                if (in_array('amc', $types)) {
                    $evalAMC = $idee->evaluationAMC->first();
                    if ($evalAMC && $evalAMC->statut == 1) {
                        if (!$dryRun) {
                            ExportEvaluationJob::dispatch($idee->id, 'amc', auth()->id() ?? 1);
                            Log::info("📈 [ExportIdeeProjetFiles] Job AMC dispatché", [
                                'idee_projet_id' => $idee->id,
                                'evaluation_id' => $evalAMC->id
                            ]);
                        }
                        $stats['amc']++;
                    }
                }

            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("❌ [ExportIdeeProjetFiles] Erreur lors du dispatch", [
                    'idee_projet_id' => $idee->id,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Afficher les statistiques
        $this->info('✅ Traitement terminé!');
        $this->newLine();
        $this->info('📊 Statistiques:');
        $this->table(
            ['Type d\'export', 'Nombre de jobs dispatchés'],
            [
                ['Fiche idée projet', $stats['fiche']],
                ['Évaluation pertinence', $stats['pertinence']],
                ['Évaluation climatique', $stats['climatique']],
                ['Évaluation AMC', $stats['amc']],
                ['Erreurs', $stats['errors']],
            ]
        );

        if (!$dryRun) {
            $this->newLine();
            $this->info('💡 Les jobs ont été ajoutés à la queue. Surveillez le queue worker pour voir leur progression:');
            $this->comment('   tail -f storage/logs/laravel.log | grep "Export"');
        } else {
            $this->newLine();
            $this->warn('⚠️ MODE DRY-RUN: Aucun job n\'a été dispatché. Relancez sans --dry-run pour dispatcher réellement.');
        }

        return 0;
    }
}
