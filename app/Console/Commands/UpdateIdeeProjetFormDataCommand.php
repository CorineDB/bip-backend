<?php

namespace App\Console\Commands;

use App\Models\IdeeProjet;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Http\Resources\DocumentResource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateIdeeProjetFormDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idees:update-formdata
                            {--limit= : Limiter le nombre d\'idées à traiter}
                            {--ids= : IDs spécifiques à traiter (séparés par des virgules)}
                            {--dry-run : Afficher ce qui sera fait sans modifier la base de données}
                            {--force : Forcer la mise à jour même si ficheIdee existe déjà}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour le formData enrichi dans ficheIdee pour toutes les idées de projet';

    protected DocumentRepositoryInterface $documentRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DocumentRepositoryInterface $documentRepository)
    {
        parent::__construct();
        $this->documentRepository = $documentRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        $limit = $this->option('limit');
        $idsOption = $this->option('ids');

        if ($isDryRun) {
            $this->warn('🔍 MODE DRY-RUN: Aucune modification ne sera effectuée');
        }

        // Construction de la requête
        $query = IdeeProjet::query()->with([
            'champs',
            'lieuxIntervention',
            'projet' // Juste charger le projet, pas besoin de ses relations
        ]);

        // Filtrer par IDs spécifiques si fournis
        if ($idsOption) {
            $ids = array_map('trim', explode(',', $idsOption));
            $query->whereIn('id', $ids);
            $this->info("Filtrage sur les IDs: " . implode(', ', $ids));
        }

        // Limiter le nombre
        if ($limit) {
            $query->limit((int) $limit);
        }

        $idees = $query->get();
        $total = $idees->count();

        if ($total === 0) {
            $this->error('❌ Aucune idée de projet trouvée');
            return Command::FAILURE;
        }

        $this->info("📊 Total d'idées de projet à traiter: {$total}");
        $this->newLine();

        if (!$isDryRun && !$this->confirm('Voulez-vous continuer ?', true)) {
            $this->warn('Opération annulée par l\'utilisateur');
            return Command::SUCCESS;
        }

        $this->newLine();
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $progressBar->setMessage('Démarrage...');
        $progressBar->start();

        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $errorDetails = [];

        foreach ($idees as $idee) {
            $progressBar->setMessage("Traitement ID: {$idee->id}");

            try {
                // Vérifier si ficheIdee existe déjà et si on ne force pas
                if (!$isForce && !empty($idee->ficheIdee) && isset($idee->ficheIdee['formData'])) {
                    // Vérifier si formData a déjà des objets enrichis
                    $hasEnrichedData = $this->hasEnrichedData($idee->ficheIdee['formData']);

                    if ($hasEnrichedData) {
                        $skipped++;
                        $progressBar->advance();
                        continue;
                    }
                }

                if (!$isDryRun) {
                    DB::beginTransaction();

                    try {
                        // Récupérer la structure ficheIdee existante ou créer une nouvelle
                        $ficheIdee = $idee->ficheIdee ?? [];

                        // Ajouter le form SEULEMENT s'il n'existe pas ou est vide
                        if (empty($ficheIdee["form"])) {
                            $ficheIdee["form"] = new DocumentResource($this->documentRepository->getFicheIdee());
                        }

                        // Mettre à jour le formData enrichi (toujours)
                        $ficheIdee["formData"] = $idee->getFormDataWithRelations();

                        // Mettre à jour
                        $idee->ficheIdee = $ficheIdee;
                        $idee->save();

                        DB::commit();
                        $updated++;

                        // Mettre à jour le Projet lié si existant
                        if ($idee->relationLoaded('projet') || $idee->projet) {
                            $this->updateProjetIfExists($idee);
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                } else {
                    // Mode dry-run: simuler la mise à jour
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors++;
                $errorDetails[] = [
                    'id' => $idee->id,
                    'titre' => $idee->titre_projet ?? 'N/A',
                    'error' => $e->getMessage()
                ];
            }

            $progressBar->advance();
        }

        $progressBar->setMessage('Terminé !');
        $progressBar->finish();
        $this->newLine(2);

        // Affichage du résumé
        $this->info('✅ Résumé de l\'opération:');
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['✅ Mises à jour réussies', $updated],
                ['⏭️  Ignorées (déjà enrichies)', $skipped],
                ['❌ Erreurs', $errors],
            ]
        );

        // Afficher les erreurs si présentes
        if ($errors > 0) {
            $this->newLine();
            $this->error("❌ Détails des erreurs ({$errors}):");
            $this->table(
                ['ID', 'Titre', 'Erreur'],
                array_map(function ($error) {
                    return [
                        $error['id'],
                        \Illuminate\Support\Str::limit($error['titre'], 40),
                        \Illuminate\Support\Str::limit($error['error'], 60),
                    ];
                }, $errorDetails)
            );
        }

        $this->newLine();

        if ($isDryRun) {
            $this->warn('🔍 MODE DRY-RUN: Aucune modification n\'a été effectuée');
            $this->info('💡 Relancez la commande sans --dry-run pour appliquer les changements');
        } else {
            $this->info('✨ Mise à jour terminée avec succès !');
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Mettre à jour le Projet lié si existant
     * Copie le formData de l'IdeeProjet vers le Projet
     */
    private function updateProjetIfExists(\App\Models\IdeeProjet $idee): void
    {
        try {
            $projet = $idee->projet;

            if (!$projet) {
                return;
            }

            DB::beginTransaction();

            try {
                // Récupérer la structure ficheIdee existante ou créer une nouvelle
                $ficheIdee = $projet->ficheIdee ?? [];

                // Ajouter le form SEULEMENT s'il n'existe pas ou est vide
                if (empty($ficheIdee["form"])) {
                    $ficheIdee["form"] = new DocumentResource($this->documentRepository->getFicheIdee());
                }

                // Copier le formData enrichi de l'IdeeProjet vers le Projet
                $ficheIdee["formData"] = $idee->ficheIdee["formData"] ?? [];

                // Mettre à jour
                $projet->ficheIdee = $ficheIdee;
                $projet->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // Logger l'erreur mais ne pas bloquer le traitement
                $this->warn("⚠️  Erreur lors de la mise à jour du Projet lié (ID: {$projet->id}): " . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Ignorer silencieusement si relation non chargée
        }
    }

    /**
     * Vérifier si formData contient déjà des données enrichies
     */
    private function hasEnrichedData($formData): bool
    {
        if (!is_array($formData) || empty($formData)) {
            return false;
        }

        // Vérifier quelques champs pour voir s'ils ont des objets enrichis
        $relationAttributes = [
            'cibles', 'odds', 'sources_financement',
            'natures_financement', 'types_financement',
            'grand_secteur', 'secteur'
        ];

        foreach ($formData as $field) {
            if (!isset($field['attribut']) || !isset($field['value'])) {
                continue;
            }

            // Si c'est un champ relationnel avec une valeur
            if (in_array($field['attribut'], $relationAttributes) && !empty($field['value'])) {
                // Vérifier si c'est un array d'objets avec {id, nom}
                if (is_array($field['value'])) {
                    $firstItem = is_array($field['value']) && count($field['value']) > 0
                        ? (is_array($field['value'][0]) ? $field['value'][0] : $field['value'])
                        : $field['value'];

                    // Si on trouve un objet avec 'id' et 'nom', c'est enrichi
                    if (is_array($firstItem) && isset($firstItem['id']) && isset($firstItem['nom'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
