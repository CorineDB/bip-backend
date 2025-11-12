<?php

namespace App\Jobs;

use App\Models\IdeeProjet;
use App\Services\ProjectExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use ZipArchive;

class ExportProjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Le nombre de fois que le job peut être tenté.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Le nombre de secondes avant timeout.
     *
     * @var int
     */
    public $timeout = 3600; // 1 heure pour les exports volumineux

    /**
     * L'action à effectuer
     */
    protected $action;

    /**
     * Les options de la commande
     */
    protected $options;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($action, $options)
    {
        $this->action = $action;
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ProjectExportService $exportService)
    {
        Log::info('Démarrage du job d\'export', [
            'action' => $this->action,
            'options' => $this->options
        ]);

        try {
            $outputDirectory = $this->options['output-dir']
                ?? storage_path('app/exports/' . date('Y-m-d_H-i-s'));

            if (!file_exists($outputDirectory)) {
                mkdir($outputDirectory, 0755, true);
            }

            $exportedFiles = [];
            $projects = $this->getProjects();

            foreach ($projects as $project) {
                $files = $this->exportProject($project, $exportService, $outputDirectory);
                $exportedFiles = array_merge($exportedFiles, $files);
            }

            // Créer ZIP si demandé
            if ($this->options['zip'] ?? false) {
                $zipFile = $this->createZipArchive($outputDirectory, $exportedFiles);
                if ($zipFile) {
                    $exportedFiles = [$zipFile];
                }
            }

            // Envoyer par email si demandé
            if ($email = $this->options['email'] ?? null) {
                $this->sendExportByEmail($email, $exportedFiles);
            }

            Log::info('Export terminé avec succès', [
                'files_count' => count($exportedFiles),
                'directory' => $outputDirectory
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw pour que le job soit marqué comme échoué
        }
    }

    /**
     * Récupérer les projets selon l'action
     */
    protected function getProjects()
    {
        switch ($this->action) {
            case 'single':
                $project = IdeeProjet::find($this->options['id']);
                return $project ? [$project] : [];

            case 'batch':
                $ids = $this->options['ids'] ?? [];
                return IdeeProjet::whereIn('id', $ids)->get();

            case 'all':
                return IdeeProjet::all();

            case 'by-status':
                $status = $this->options['status'] ?? null;
                return $status ? IdeeProjet::where('statut', $status)->get() : collect();

            case 'by-date':
                $from = $this->options['from'] ?? now()->subMonth();
                $to = $this->options['to'] ?? now();
                return IdeeProjet::whereBetween('created_at', [$from, $to])->get();

            default:
                return collect();
        }
    }

    /**
     * Exporter un projet
     */
    protected function exportProject(IdeeProjet $project, ProjectExportService $exportService, $outputDirectory)
    {
        $format = $this->options['format'] ?? 'pdf';
        $withToc = ($this->options['with-toc'] ?? 'true') !== 'false';
        $language = $this->options['language'] ?? 'fr';
        $template = $this->options['template'] ?? null;

        $exportService->setLanguage($language);
        $exportService->setWithTableOfContents($withToc);

        if ($template) {
            $exportService->setTemplate($template);
        }

        $exportedFiles = [];

        // Export PDF
        if (in_array($format, ['pdf', 'both'])) {
            $filename = $this->generateFilename($project, 'pdf');
            $filepath = $outputDirectory . '/' . $filename;

            $pdf = $exportService->generatePdf($project);
            file_put_contents($filepath, $pdf->output());

            $exportedFiles[] = $filepath;

            Log::info("PDF exporté : {$filename}");
        }

        // Export Word
        if (in_array($format, ['word', 'both'])) {
            $filename = $this->generateFilename($project, 'docx');
            $filepath = $outputDirectory . '/' . $filename;

            $exportService->generateWord($project, $filepath);

            $exportedFiles[] = $filepath;

            Log::info("Word exporté : {$filename}");
        }

        return $exportedFiles;
    }

    /**
     * Générer le nom de fichier
     */
    protected function generateFilename(IdeeProjet $project, $extension)
    {
        $bip = $project->bip_number ?: 'DRAFT';
        $title = \Str::slug($project->title);
        $date = now()->format('Ymd');

        return "fiche_projet_{$bip}_{$title}_{$date}.{$extension}";
    }

    /**
     * Créer une archive ZIP
     */
    protected function createZipArchive($outputDirectory, $files)
    {
        $zipFile = $outputDirectory . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $zip->addFile($file, basename($file));
                }
            }

            $zip->close();

            Log::info("Archive ZIP créée : {$zipFile}");

            return $zipFile;
        }

        return null;
    }

    /**
     * Envoyer l'export par email
     */
    protected function sendExportByEmail($email, $files)
    {
        Mail::send('emails.export-notification', [
            'count' => count($files),
            'date' => now()->format('d/m/Y H:i')
        ], function ($message) use ($email, $files) {
            $message->to($email)
                ->subject('Export des fiches de projet - ' . now()->format('d/m/Y H:i'));

            foreach ($files as $file) {
                if (file_exists($file)) {
                    $message->attach($file);
                }
            }
        });

        Log::info("Export envoyé par email à : {$email}");
    }

    /**
     * Gérer l'échec du job
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Le job d\'export a échoué', [
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);

        // Notifier l'administrateur si configuré
        if ($adminEmail = config('app.admin_email')) {
            Mail::raw(
                "Le job d'export a échoué.\n\nErreur: " . $exception->getMessage(),
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('Échec du job d\'export - ' . now()->format('d/m/Y H:i'));
                }
            );
        }
    }
}
