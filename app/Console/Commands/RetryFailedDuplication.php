<?php

namespace App\Console\Commands;

use App\Events\IdeeProjetTransformee;
use App\Models\IdeeProjet;
use Illuminate\Console\Command;

class RetryFailedDuplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projet:retry-failed-duplication {--statut=03a_NoteConceptuel : Le statut des idées à relancer} {--dry-run : Afficher les idées sans les traiter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Relance la duplication des IdeeProjet vers Projet pour les idées qui ont échoué';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $statut = $this->option('statut');
        $dryRun = $this->option('dry-run');

        $this->info("Recherche des IdeeProjet avec statut '{$statut}' sans Projet associé...");

        $idees = IdeeProjet::where("statut", $statut)
            ->whereDoesntHave("projet")
            ->get();

        $count = $idees->count();

        if ($count === 0) {
            $this->info('Aucune IdeeProjet à traiter.');
            return 0;
        }

        $this->info("Trouvé {$count} IdeeProjet à traiter.");

        if ($dryRun) {
            $this->table(
                ['ID', 'Titre', 'Statut', 'Créé le'],
                $idees->map(function($idee) {
                    return [
                        $idee->id,
                        $idee->titre_projet,
                        $idee->statut,
                        $idee->created_at->format('Y-m-d H:i:s')
                    ];
                })
            );
            $this->info('Mode dry-run activé. Aucune action effectuée.');
            return 0;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($idees as $idee) {
            try {
                event(new IdeeProjetTransformee($idee));
                $success++;
                $bar->setMessage("Traitement de l'IdeeProjet {$idee->id} - {$idee->titre_projet}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("Erreur pour IdeeProjet {$idee->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Traitement terminé:");
        $this->info("- Succès: {$success}");
        if ($failed > 0) {
            $this->error("- Échecs: {$failed}");
        }

        return $failed > 0 ? 1 : 0;
    }
}
