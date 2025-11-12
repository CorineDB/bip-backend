<?php

namespace App\Console\Commands;

use App\Models\IdeeProjet;
use App\Services\ProjectExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class ExportProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:export
                            {action : Action à effectuer (single|batch|all|by-status|by-date)}
                            {--id= : ID du projet pour export unique}
                            {--ids=* : IDs des projets pour export en lot}
                            {--format=pdf : Format d\'export (pdf|word|both)}
                            {--status= : Statut des projets à exporter}
                            {--from= : Date de début (format: Y-m-d)}
                            {--to= : Date de fin (format: Y-m-d)}
                            {--output-dir= : Répertoire de sortie personnalisé}
                            {--zip : Compresser les fichiers dans une archive ZIP}
                            {--email= : Envoyer les fichiers par email à cette adresse}
                            {--queue : Exécuter l\'export en arrière-plan}
                            {--with-toc : Inclure la table des matières (activé par défaut)}
                            {--language=fr : Langue du document (fr|en)}
                            {--template= : Template personnalisé à utiliser}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporter les fiches de projet en PDF ou Word avec table des matières';

    /**
     * Service d'exportation
     */
    protected $exportService;

    /**
     * Répertoire de sortie
     */
    protected $outputDirectory;

    /**
     * Compteurs pour le rapport
     */
    protected $stats = [
        'success' => 0,
        'failed' => 0,
        'total' => 0
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProjectExportService $exportService)
    {
        parent::__construct();
        $this->exportService = $exportService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║           EXPORTATION DES FICHES DE PROJET              ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Définir le répertoire de sortie
        $this->outputDirectory = $this->option('output-dir')
            ?? storage_path('app/exports/' . date('Y-m-d_H-i-s'));

        // Créer le répertoire s'il n'existe pas
        if (!file_exists($this->outputDirectory)) {
            mkdir($this->outputDirectory, 0755, true);
        }

        // Exécuter en file d'attente si demandé
        if ($this->option('queue')) {
            return $this->dispatchJob();
        }

        // Exécuter l'action demandée
        $action = $this->argument('action');

        switch ($action) {
            case 'single':
                return $this->exportSingle();
            case 'batch':
                return $this->exportBatch();
            case 'all':
                return $this->exportAll();
            case 'by-status':
                return $this->exportByStatus();
            case 'by-date':
                return $this->exportByDate();
            default:
                $this->error("Action inconnue : {$action}");
                return 1;
        }
    }

    /**
     * Exporter un seul projet
     */
    protected function exportSingle()
    {
        $projectId = $this->option('id');

        if (!$projectId) {
            // Mode interactif
            $projects = IdeeProjet::select('id', 'titre_projet', 'identifiant_bip')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $choices = $projects->map(function ($p) {
                return "[{$p->id}] {$p->titre_projet} (BIP: {$p->identifiant_bip})";
            })->toArray();

            $selected = $this->choice(
                'Sélectionnez le projet à exporter',
                $choices
            );

            preg_match('/\[(\d+)\]/', $selected, $matches);
            $projectId = $matches[1] ?? null;
        }

        $project = IdeeProjet::with([
            'ministere',
            'odds',
            'cibles',
            'orientations_strategique_png',
            'objectifs_strategique_png',
            'piliers_pag',
            'axes_pag',
            'actions_pag'
        ])->find($projectId);

        if (!$project) {
            $this->error("Projet introuvable avec l'ID : {$projectId}");
            return 1;
        }

        $this->info("Export du projet : {$project->titre_projet}");
        $this->newLine();

        return $this->exportProject($project);
    }

    /**
     * Exporter plusieurs projets
     */
    protected function exportBatch()
    {
        $ids = $this->option('ids');

        if (empty($ids)) {
            // Mode interactif avec sélection multiple
            $projects = IdeeProjet::select('id', 'titre_projet', 'identifiant_bip')
                ->orderBy('created_at', 'desc')
                ->get();

            $choices = $projects->map(function ($p) {
                return "[{$p->id}] {$p->titre_projet} (BIP: {$p->identifiant_bip})";
            })->toArray();

            $selected = $this->choice(
                'Sélectionnez les projets à exporter (séparés par des virgules)',
                $choices,
                null,
                null,
                true
            );

            $ids = [];
            foreach ($selected as $item) {
                preg_match('/\[(\d+)\]/', $item, $matches);
                if (isset($matches[1])) {
                    $ids[] = $matches[1];
                }
            }
        }

        $projects = IdeeProjet::whereIn('id', $ids)->get();

        if ($projects->isEmpty()) {
            $this->error('Aucun projet trouvé avec les IDs fournis');
            return 1;
        }

        $this->info("Export de {$projects->count()} projets...");
        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        foreach ($projects as $project) {
            $this->exportProject($project, false);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $this->finalize();
    }

    /**
     * Exporter tous les projets
     */
    protected function exportAll()
    {
        $confirm = $this->confirm(
            'Êtes-vous sûr de vouloir exporter TOUS les projets ?'
        );

        if (!$confirm) {
            $this->info('Export annulé.');
            return 0;
        }

        $projects = IdeeProjet::all();

        if ($projects->isEmpty()) {
            $this->warn('Aucun projet dans la base de données.');
            return 0;
        }

        $this->info("Export de {$projects->count()} projets...");
        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        foreach ($projects as $project) {
            $this->exportProject($project, false);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $this->finalize();
    }

    /**
     * Exporter par statut
     */
    protected function exportByStatus()
    {
        $status = $this->option('status');

        if (!$status) {
            // Récupérer les statuts disponibles
            $statuses = IdeeProjet::distinct()->pluck('statut')->filter()->toArray();

            if (empty($statuses)) {
                $this->warn('Aucun statut disponible dans la base de données.');
                return 0;
            }

            $status = $this->choice('Sélectionnez le statut', $statuses);
        }

        $projects = IdeeProjet::where('statut', $status)->get();

        if ($projects->isEmpty()) {
            $this->warn("Aucun projet avec le statut : {$status}");
            return 0;
        }

        $this->info("Export de {$projects->count()} projets avec le statut '{$status}'...");
        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        foreach ($projects as $project) {
            $this->exportProject($project, false);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $this->finalize();
    }

    /**
     * Exporter par plage de dates
     */
    protected function exportByDate()
    {
        $from = $this->option('from');
        $to = $this->option('to');

        if (!$from) {
            $from = $this->ask('Date de début (YYYY-MM-DD)', Carbon::now()->subMonth()->format('Y-m-d'));
        }

        if (!$to) {
            $to = $this->ask('Date de fin (YYYY-MM-DD)', Carbon::now()->format('Y-m-d'));
        }

        try {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to)->endOfDay();
        } catch (\Exception $e) {
            $this->error('Format de date invalide. Utilisez le format YYYY-MM-DD');
            return 1;
        }

        $projects = IdeeProjet::whereBetween('created_at', [$fromDate, $toDate])->get();

        if ($projects->isEmpty()) {
            $this->warn("Aucun projet trouvé entre {$from} et {$to}");
            return 0;
        }

        $this->info("Export de {$projects->count()} projets créés entre {$from} et {$to}...");
        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        foreach ($projects as $project) {
            $this->exportProject($project, false);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $this->finalize();
    }

    /**
     * Exporter un projet individual
     */
    protected function exportProject(IdeeProjet $project, $showOutput = true)
    {
        $this->stats['total']++;
        $format = $this->option('format');

        try {
            $exportedFiles = [];

            // Export PDF
            if (in_array($format, ['pdf', 'both'])) {
                $filename = $this->generateFilename($project, 'pdf');
                $filepath = $this->outputDirectory . '/' . $filename;

                $response = $this->exportService->exportToPdf($project);
                file_put_contents($filepath, $response->getContent());

                $exportedFiles[] = $filepath;

                if ($showOutput) {
                    $this->info("✓ PDF exporté : {$filename}");
                }
            }

            // Export Word
            if (in_array($format, ['word', 'both'])) {
                $filename = $this->generateFilename($project, 'docx');
                $filepath = $this->outputDirectory . '/' . $filename;

                $response = $this->exportService->exportToWord($project);
                copy($response->getFile()->getPathname(), $filepath);

                $exportedFiles[] = $filepath;

                if ($showOutput) {
                    $this->info("✓ Word exporté : {$filename}");
                }
            }

            $this->stats['success']++;

            // Log l'export
            $this->logExport($project, $exportedFiles, 'success');

            return $exportedFiles;

        } catch (\Exception $e) {
            $this->stats['failed']++;

            if ($showOutput) {
                $this->error("✗ Erreur lors de l'export du projet {$project->id}: " . $e->getMessage());
            }

            // Log l'erreur
            $this->logExport($project, [], 'failed', $e->getMessage());

            return [];
        }
    }

    /**
     * Générer un nom de fichier
     */
    protected function generateFilename(IdeeProjet $project, $extension)
    {
        $bip = $project->identifiant_bip ?: 'DRAFT';
        $titre_projet = \Str::slug($project->titre_projet);
        $date = now()->format('Ymd');

        return "fiche_projet_{$bip}_{$titre_projet}_{$date}.{$extension}";
    }

    /**
     * Finaliser l'export (ZIP, email, rapport)
     */
    protected function finalize()
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║                    RAPPORT D\'EXPORT                     ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');

        // Afficher les statistiques
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Total traité', $this->stats['total']],
                ['Succès', $this->stats['success']],
                ['Échecs', $this->stats['failed']],
                ['Taux de réussite', $this->stats['total'] > 0
                    ? round(($this->stats['success'] / $this->stats['total']) * 100, 2) . '%'
                    : 'N/A'],
            ]
        );

        // Créer une archive ZIP si demandé
        if ($this->option('zip')) {
            $this->createZipArchive();
        }

        // Envoyer par email si demandé
        if ($email = $this->option('email')) {
            $this->sendByEmail($email);
        }

        $this->newLine();
        $this->info("📁 Fichiers exportés dans : {$this->outputDirectory}");

        // Générer un rapport CSV
        $this->generateReport();

        return $this->stats['failed'] > 0 ? 1 : 0;
    }

    /**
     * Créer une archive ZIP
     */
    protected function createZipArchive()
    {
        $this->info('Création de l\'archive ZIP...');

        $zipFile = $this->outputDirectory . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            $files = glob($this->outputDirectory . '/*');

            foreach ($files as $file) {
                if (is_file($file)) {
                    $zip->addFile($file, basename($file));
                }
            }

            $zip->close();
            $this->info("✓ Archive créée : " . basename($zipFile));

            // Option pour supprimer les fichiers individuels
            if ($this->confirm('Supprimer les fichiers individuels ?', false)) {
                array_map('unlink', $files);
                rmdir($this->outputDirectory);
                $this->info('Fichiers individuels supprimés.');
            }
        } else {
            $this->error('Impossible de créer l\'archive ZIP');
        }
    }

    /**
     * Envoyer les exports par email
     */
    protected function sendByEmail($email)
    {
        $this->info("Envoi des fichiers à {$email}...");

        try {
            // Créer d'abord une archive ZIP
            $zipFile = sys_get_temp_dir() . '/export_' . time() . '.zip';
            $zip = new ZipArchive();

            if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
                $files = glob($this->outputDirectory . '/*');

                foreach ($files as $file) {
                    if (is_file($file)) {
                        $zip->addFile($file, basename($file));
                    }
                }

                $zip->close();

                // Envoyer l'email avec pièce jointe
                \Mail::raw('Veuillez trouver ci-joint l\'export des fiches de projet demandé.', function ($message) use ($email, $zipFile) {
                    $message->to($email)
                        ->subject('Export des fiches de projet - ' . now()->format('d/m/Y H:i'))
                        ->attach($zipFile, [
                            'as' => 'fiches_projet_' . date('Ymd_His') . '.zip',
                            'mime' => 'application/zip',
                        ]);
                });

                // Supprimer le fichier temporaire
                unlink($zipFile);

                $this->info("✓ Email envoyé à {$email}");
            }
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi de l'email : " . $e->getMessage());
        }
    }

    /**
     * Générer un rapport d'export
     */
    protected function generateReport()
    {
        $reportFile = $this->outputDirectory . '/rapport_export_' . date('Ymd_His') . '.csv';

        $handle = fopen($reportFile, 'w');
        fputcsv($handle, ['Date', 'Heure', 'Total', 'Succès', 'Échecs', 'Format', 'Répertoire']);
        fputcsv($handle, [
            date('Y-m-d'),
            date('H:i:s'),
            $this->stats['total'],
            $this->stats['success'],
            $this->stats['failed'],
            $this->option('format'),
            $this->outputDirectory
        ]);
        fclose($handle);

        $this->info('✓ Rapport généré : ' . basename($reportFile));
    }

    /**
     * Logger les exports
     */
    protected function logExport(IdeeProjet $project, array $files, $status, $error = null)
    {
        $logFile = storage_path('logs/project_exports.log');

        $logEntry = [
            'timestamp' => now()->toIso8601String(),
            'project_id' => $project->id,
            'identifiant_bip' => $project->identifiant_bip,
            'titre_projet' => $project->titre_projet,
            'status' => $status,
            'files' => $files,
            'error' => $error,
            'user' => get_current_user(),
            'command' => $this->getName()
        ];

        file_put_contents(
            $logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Dispatcher le job en arrière-plan
     */
    protected function dispatchJob()
    {
        $this->info('Export en cours de traitement en arrière-plan...');

        // Dispatcher le job
        \App\Jobs\ExportProjectJob::dispatch(
            $this->argument('action'),
            $this->options()
        );

        $this->info('✓ Job ajouté à la file d\'attente');
        $this->info('Utilisez "php artisan queue:work" pour traiter la file d\'attente');

        return 0;
    }
}
