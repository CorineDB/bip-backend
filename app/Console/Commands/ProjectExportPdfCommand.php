<?php

namespace App\Console\Commands;

use App\Models\IdeeProjet;
use App\Services\ProjectExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

class ProjectExportPdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:export-pdf {projet_id} {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporter un projet en PDF';

    protected ProjectExportService $projectExportService;

    public function __construct(ProjectExportService $projectExportService)
    {
        parent::__construct();
        $this->projectExportService = $projectExportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projetId = $this->argument('projet_id');
        $outputPath = $this->option('path');

        // Décodage de l'ID si c'est un hash
        $decodedId = Hashids::decode($projetId);
        $actualId = !empty($decodedId) ? $decodedId[0] : $projetId;

        $projet = IdeeProjet::with([
            'ministere',
            'odds',
            'cibles',
            'orientations_strategique_pnd',
            'objectifs_strategique_pnd',
            'piliers_pag',
            'axes_pag',
            'actions_pag'
        ])->find($actualId);

        if (!$projet) {
            $this->error("Projet non trouvé avec l'ID : {$projetId}");
            return 1;
        }

        $this->info("Exportation du projet '{$projet->titre_projet}' en cours...");

        // Appeler exportToPdf qui gère maintenant le stockage automatiquement
        $this->projectExportService->exportToPdf($projet);

        $this->info("Le projet a été exporté avec succès (fiche stockée dans les fichiers du projet)");

        return 0;
    }
}
