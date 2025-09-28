<?php

namespace Database\Seeders;

use App\Http\Resources\CanevasAppreciationTdrResource;
use Illuminate\Database\Seeder;
use App\Models\NoteConceptuelle;
use App\Models\IdeeProjet;
use App\Models\Tdr;
use App\Models\Rapport;
use App\Models\Secteur;
use App\Repositories\DocumentRepository;
use App\Repositories\CategorieCritereRepository;
use App\Http\Resources\CanevasNoteConceptuelleResource;
use App\Http\Resources\CategorieCritereResource;
use App\Http\Resources\ChecklistMesuresAdaptationSecteurResource;
use App\Enums\StatutIdee;
use Illuminate\Support\Facades\Log;

class UpdateNotesConceptuellesCanevasAppreciationSeeder extends Seeder
{
    protected $documentRepository;
    protected $categorieCritereRepository;

    public function __construct(DocumentRepository $documentRepository, CategorieCritereRepository $categorieCritereRepository)
    {
        $this->documentRepository = $documentRepository;
        $this->categorieCritereRepository = $categorieCritereRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $this->command->info('🚀 Début de la mise à jour des canevas d\'appréciation des notes conceptuelles...');

            // Récupérer le canevas d'appréciation des notes conceptuelles
            $canevasAppreciation = $this->documentRepository->getCanevasAppreciationNoteConceptuelle();

            if (!$canevasAppreciation) {
                $this->command->error('❌ Aucun canevas d\'appréciation des notes conceptuelles trouvé.');
                return;
            }
            $this->command->info("✅ Canevas d'appréciation trouvé: {$canevasAppreciation->titre}");


            $canevasRedactionNC = $this->documentRepository->getCanevasRedactionNoteConceptuelle();

            if (!$canevasRedactionNC) {
                $this->command->error('❌ Aucun canevas de redaction des notes conceptuelles trouvé.');
                return;
            }

            $canevasRedactionNCnResource = $canevasRedactionNC ? (new CanevasAppreciationTdrResource($canevasRedactionNC)) : null;

            $canevasAppreciationResource = $canevasAppreciation ? new CanevasNoteConceptuelleResource($canevasAppreciation) : null;

            $this->command->info("✅ Canevas de rédaction trouvé: {$canevasAppreciation->titre}");


            //$canevasStructure = $canevasResource->toArray(request());

            /**
             * Query multiple records update
             *
             */
            // Mise à jour des Notes Conceptuelles
            $nbNotesConceptuelles = NoteConceptuelle::query()->update([
                'canevas_redaction_note_conceptuelle' => $canevasRedactionNCnResource->toArray(request()),
                'canevas_appreciation_note_conceptuelle' => $canevasAppreciationResource->toArray(request())
            ]);

            $this->command->info("✅ {$nbNotesConceptuelles} notes conceptuelles mises à jour.");

            // Mise à jour des Idées de Projet avec le canevas climatique
            $this->command->info('🌍 Début de la mise à jour des canevas climatiques des idées de projet...');

            // Récupérer le canevas d'évaluation climatique
            $grilleEvaluation = $this->categorieCritereRepository->getCanevasEvaluationClimatique();

            if (!$grilleEvaluation) {
                $this->command->error('❌ Aucun canevas d\'évaluation climatique trouvé.');
            } else {
                $this->command->info("✅ Canevas d'évaluation climatique trouvé: {$grilleEvaluation->intitule}");

                // Créer la structure du canevas climatique
                $canevasClimatiqueResource = new CategorieCritereResource($grilleEvaluation);

                // Mise à jour des idées de projet éligibles
                // (est_soumise = true et statut n'est pas 00_brouillon)
                $ideesEligibles = IdeeProjet::where('est_soumise', true)
                    ->where('statut', '!=', StatutIdee::BROUILLON)
                    ->get();

                $nbIdeesProjet = 0;
                $nbProjetsLies = 0;

                foreach ($ideesEligibles as $idee) {
                    // Mettre à jour l'idée de projet
                    $idee->update([
                        'canevas_climatique' => $canevasClimatiqueResource->toArray(request())
                    ]);
                    $nbIdeesProjet++;

                    // Vérifier si l'idée est liée à un projet
                    if ($idee->projet) {
                        $idee->projet->update([
                            'canevas_climatique' => $canevasClimatiqueResource->toArray(request())
                        ]);
                        $nbProjetsLies++;
                        $this->command->line("   🔗 Projet lié ID {$idee->projet->id} - '{$idee->projet->intitule}' mis à jour.");
                    }
                }

                $this->command->info("✅ {$nbIdeesProjet} idées de projet mises à jour avec le canevas climatique.");
                if ($nbProjetsLies > 0) {
                    $this->command->info("✅ {$nbProjetsLies} projets liés mis à jour avec le canevas climatique.");
                }

                // Mise à jour du canevas AMC pour les idées avec statut spécifique
                $this->command->info('🎯 Début de la mise à jour du canevas AMC pour les idées éligibles...');

                // Récupérer le canevas AMC
                $grilleEvaluationAMC = $this->categorieCritereRepository->getCanevasAMC();

                if (!$grilleEvaluationAMC) {
                    $this->command->error('❌ Aucun canevas AMC trouvé.');
                } else {
                    $this->command->info("✅ Canevas AMC trouvé: {$grilleEvaluationAMC->intitule}");

                    // Créer la structure du canevas AMC
                    $canevasAmcResource = new CategorieCritereResource($grilleEvaluationAMC);

                    // Récupérer les idées avec statut 02c_validation ou 03a_NoteConceptuel
                    $ideesAmcEligibles = IdeeProjet::where('est_soumise', true)
                        ->where('statut', '!=', StatutIdee::BROUILLON)
                        ->whereIn('statut', ['02c_validation', '03a_NoteConceptuel'])
                        ->get();

                    $nbIdeesAmc = 0;
                    $nbProjetsLiesAmc = 0;

                    foreach ($ideesAmcEligibles as $idee) {
                        // Mettre à jour l'idée de projet avec le canevas AMC
                        $idee->update([
                            'canevas_amc' => $canevasAmcResource->toArray(request())
                        ]);
                        $nbIdeesAmc++;
                        $this->command->line("   🎯 Idée ID {$idee->id} (statut: {$idee->statut->value}) - '{$idee->intitule}' mise à jour avec canevas AMC.");

                        // Vérifier si l'idée est liée à un projet
                        if ($idee->projet) {
                            $idee->projet->update([
                                'canevas_amc' => $canevasAmcResource->toArray(request())
                            ]);
                            $nbProjetsLiesAmc++;
                            $this->command->line("   🔗 Projet lié ID {$idee->projet->id} - '{$idee->projet->intitule}' mis à jour avec canevas AMC.");
                        }
                    }

                    $this->command->info("✅ {$nbIdeesAmc} idées de projet mises à jour avec le canevas AMC.");
                    if ($nbProjetsLiesAmc > 0) {
                        $this->command->info("✅ {$nbProjetsLiesAmc} projets liés mis à jour avec le canevas AMC.");
                    }

                    // Mise à jour des statistiques pour inclure AMC
                    $this->command->info('📊 STATISTIQUES CANEVAS AMC:');
                    $this->command->line("   🎯 Idées éligibles AMC: {$ideesAmcEligibles->count()}");
                    $this->command->line("   ✅ Idées mises à jour AMC: {$nbIdeesAmc}");
                    $this->command->line("   🔗 Projets liés mis à jour AMC: {$nbProjetsLiesAmc}");
                }

                // Mise à jour des TDRs avec leurs canevas d'appréciation
                $this->command->info('📋 Début de la mise à jour des canevas d\'appréciation des TDRs...');

                // Récupérer les canevas d'appréciation TDR
                $canevasTdrPrefaisabilite = $this->documentRepository->getCanevasAppreciationTdrPrefaisabilite();
                $canevasTdrFaisabilite = $this->documentRepository->getCanevasAppreciationTdrFaisabilite();

                $nbTdrsPrefaisabilite = 0;
                $nbTdrsFaisabilite = 0;

                if ($canevasTdrPrefaisabilite) {
                    $this->command->info("✅ Canevas TDR préfaisabilité trouvé: {$canevasTdrPrefaisabilite->titre}");
                    $canevasPrefaisabiliteResource = new CanevasAppreciationTdrResource($canevasTdrPrefaisabilite);

                    // Mettre à jour les TDRs de type préfaisabilité
                    $nbTdrsPrefaisabilite = Tdr::where('type', 'prefaisabilite')
                        ->update([
                            'canevas_appreciation_tdr' => $canevasPrefaisabiliteResource->toArray(request())
                        ]);

                    $this->command->info("✅ {$nbTdrsPrefaisabilite} TDRs de préfaisabilité mis à jour.");
                } else {
                    $this->command->error('❌ Aucun canevas d\'appréciation TDR préfaisabilité trouvé.');
                }

                if ($canevasTdrFaisabilite) {
                    $this->command->info("✅ Canevas TDR faisabilité trouvé: {$canevasTdrFaisabilite->titre}");
                    $canevasFaisabiliteResource = new CanevasAppreciationTdrResource($canevasTdrFaisabilite);

                    // Mettre à jour les TDRs de type faisabilité
                    $nbTdrsFaisabilite = Tdr::where('type', 'faisabilite')
                        ->update([
                            'canevas_appreciation_tdr' => $canevasFaisabiliteResource->toArray(request())
                        ]);

                    $this->command->info("✅ {$nbTdrsFaisabilite} TDRs de faisabilité mis à jour.");
                } else {
                    $this->command->error('❌ Aucun canevas d\'appréciation TDR faisabilité trouvé.');
                }

                // Statistiques TDRs
                $totalTdrs = Tdr::count();
                $tdrsPrefaisabilite = Tdr::where('type', 'prefaisabilite')->count();
                $tdrsFaisabilite = Tdr::where('type', 'faisabilite')->count();

                $this->command->info('📊 STATISTIQUES DES TDRs:');
                $this->command->line("   📋 Total TDRs: {$totalTdrs}");
                $this->command->line("   📝 TDRs préfaisabilité: {$tdrsPrefaisabilite}");
                $this->command->line("   🔬 TDRs faisabilité: {$tdrsFaisabilite}");
                $this->command->line("   ✅ TDRs préfaisabilité mis à jour: {$nbTdrsPrefaisabilite}");
                $this->command->line("   ✅ TDRs faisabilité mis à jour: {$nbTdrsFaisabilite}");

                // Mise à jour des Rapports
                $this->command->info('📊 Début de la mise à jour des canevas des rapports...');

                // Mise à jour des rapports de préfaisabilité
                $this->updateRapportsPrefaisabilite();

                // Mise à jour des rapports de faisabilité
                $this->updateRapportsFaisabilite();

                // Afficher les statistiques
                $totalIdees = IdeeProjet::count();
                $ideesSoumises = IdeeProjet::where('est_soumise', true)->count();
                $countIdeesEligibles = IdeeProjet::where('est_soumise', true)
                    ->where('statut', '!=', StatutIdee::BROUILLON)
                    ->count();

                $this->command->info('📊 STATISTIQUES DES IDÉES DE PROJET:');
                $this->command->line("   📈 Total idées: {$totalIdees}");
                $this->command->line("   📤 Idées soumises: {$ideesSoumises}");
                $this->command->line("   ✅ Idées éligibles: {$countIdeesEligibles}");
                $this->command->line("   🌍 Idées mises à jour: {$nbIdeesProjet}");
                $this->command->line("   🔗 Projets liés mis à jour: {$nbProjetsLies}");
            }

        } catch (\Exception $e) {
            $this->command->error('❌ Erreur générale lors de la mise à jour: ' . $e->getMessage());
            Log::error('Erreur UpdateNotesConceptuellesCanevasAppreciationSeeder::run', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Mise à jour des rapports de préfaisabilité
     */
    private function updateRapportsPrefaisabilite(): void
    {
        $this->command->info('📋 Début de la mise à jour des rapports de préfaisabilité...');

        // Récupérer le canevas de checklist de suivi pour préfaisabilité
        $canevasChecklistSuivi = $this->documentRepository->getCanevasChecklistSuiviRapportPrefaisabilite();

        if (!$canevasChecklistSuivi) {
            $this->command->error('❌ Aucun canevas de checklist de suivi pour préfaisabilité trouvé.');
            return;
        }

        $this->command->info("✅ Canevas checklist suivi préfaisabilité trouvé: {$canevasChecklistSuivi->titre}");

        // Récupérer le canevas des mesures d'adaptation
        $canevasMesuresAdaptation = $this->categorieCritereRepository->findByAttribute('slug', 'checklist-mesures-adaptation-haut-risque');

        if (!$canevasMesuresAdaptation) {
            $this->command->error('❌ Aucun canevas de mesures d\'adaptation trouvé.');
            return;
        }

        $this->command->info("✅ Canevas mesures d'adaptation trouvé: {$canevasMesuresAdaptation->intitule}");

        // Charger les rapports de préfaisabilité avec leurs projets et secteurs
        $rapportsPrefaisabilite = Rapport::where('type', 'prefaisabilite')
            ->with(['projet.secteur'])
            ->get();

        $nbRapportsTraites = 0;
        $nbRapportsErreur = 0;

        foreach ($rapportsPrefaisabilite as $rapport) {
            try {
                // Vérifier que le rapport a un projet avec un secteur
                if (!$rapport->projet || !$rapport->projet->secteurId) {
                    $this->command->line("   ⚠️ Rapport ID {$rapport->id} ignoré (pas de secteur associé)");
                    continue;
                }

                $secteurId = $rapport->projet->secteurId;

                // Déterminer l'ID du secteur à utiliser pour le filtrage
                $secteur = Secteur::whereIn('type', ['secteur', 'sous-secteur'])->find($secteurId);

                if (!$secteur) {
                    $this->command->line("   ⚠️ Rapport ID {$rapport->id} ignoré (secteur non trouvé)");
                    continue;
                }

                $secteurIdPourFiltrage = $secteurId;

                // Si c'est un sous-secteur, récupérer son secteur parent pour le filtrage
                if ($secteur->type->value === 'sous-secteur') {
                    $secteurParent = $secteur->parent;
                    if ($secteurParent) {
                        $secteurIdPourFiltrage = $secteurParent->id;
                    }
                }

                // Charger la checklist avec les critères et notations filtrés par secteur
                $canevasMesuresAdaptation->load([
                    'criteres' => function($query) use ($secteurIdPourFiltrage) {
                        $query->withNotationsDuSecteur($secteurIdPourFiltrage);
                    },
                    'fichiers'
                ]);

                // Créer les resources pour les canevas
                $canevasChecklistSuiviResource = new CanevasAppreciationTdrResource($canevasChecklistSuivi);
                $canevasMesuresAdaptationResource = new ChecklistMesuresAdaptationSecteurResource($canevasMesuresAdaptation);

                // Mettre à jour le rapport
                $rapport->update([
                    'checklist_suivi_rapport_prefaisabilite' => $canevasChecklistSuiviResource->toArray(request()),
                    'checklists_mesures_adaptation_haut_risque' => $canevasMesuresAdaptationResource->toArray(request())
                ]);

                $nbRapportsTraites++;
                $this->command->line("   ✅ Rapport ID {$rapport->id} - '{$rapport->intitule}' mis à jour (secteur: {$secteur->nom})");

            } catch (\Exception $e) {
                $nbRapportsErreur++;
                $this->command->line("   ❌ Erreur rapport ID {$rapport->id}: {$e->getMessage()}");
                Log::error('Erreur mise à jour rapport préfaisabilité', [
                    'rapport_id' => $rapport->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->command->info("✅ {$nbRapportsTraites} rapports de préfaisabilité mis à jour.");
        if ($nbRapportsErreur > 0) {
            $this->command->error("❌ {$nbRapportsErreur} rapports ont eu des erreurs.");
        }

        // Statistiques
        $totalRapportsPrefaisabilite = Rapport::where('type', 'prefaisabilite')->count();
        $this->command->info('📊 STATISTIQUES RAPPORTS PRÉFAISABILITÉ:');
        $this->command->line("   📋 Total rapports préfaisabilité: {$totalRapportsPrefaisabilite}");
        $this->command->line("   ✅ Rapports traités: {$nbRapportsTraites}");
        $this->command->line("   ❌ Erreurs: {$nbRapportsErreur}");
    }

    /**
     * Mise à jour des rapports de faisabilité
     */
    private function updateRapportsFaisabilite(): void
    {
        $this->command->info('🔬 Début de la mise à jour des rapports de faisabilité...');

        // Mapping des colonnes et méthodes du repository
        $canevasMapping = [
            'checklist_suivi_assurance_qualite_rapport_etude_faisabilite' => 'getCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite',
            'checklist_etude_faisabilite_technique' => 'getCanevasChecklisteEtudeFaisabiliteTechnique',
            'checklist_etude_faisabilite_economique' => 'getCanevasChecklisteEtudeFaisabiliteEconomique',
            'checklist_etude_faisabilite_marche' => 'getCanevasChecklisteEtudeFaisabiliteMarche',
            'checklist_etude_faisabilite_organisationnelle_et_juridique' => 'getCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique',
            'checklist_suivi_analyse_faisabilite_financiere' => 'getCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere',
            'checklist_suivi_etude_analyse_impact_environnementale_et_sociale' => 'getCanevasChecklisteSuiviEtudeImpactEnvironnementaleEtSociale',
        ];

        // Récupérer tous les canevas
        $canevas = [];
        $canevasManquants = [];

        foreach ($canevasMapping as $colonne => $methode) {
            $canevasDocument = $this->documentRepository->$methode();
            if ($canevasDocument) {
                $canevas[$colonne] = new CanevasAppreciationTdrResource($canevasDocument);
                $this->command->info("✅ Canevas '{$colonne}' trouvé: {$canevasDocument->titre}");
            } else {
                $canevasManquants[] = $colonne;
                $this->command->error("❌ Canevas '{$colonne}' non trouvé.");
            }
        }

        if (empty($canevas)) {
            $this->command->error('❌ Aucun canevas de faisabilité trouvé. Abandon de la mise à jour.');
            return;
        }

        // Mettre à jour les rapports de faisabilité
        $rapportsFaisabilite = Rapport::where('type', 'faisabilite')->get();
        $nbRapportsTraites = 0;

        foreach ($rapportsFaisabilite as $rapport) {
            try {
                $updateData = [];

                // Préparer les données de mise à jour
                foreach ($canevas as $colonne => $canevasResource) {
                    $updateData[$colonne] = $canevasResource->toArray(request());
                }

                // Mettre à jour le rapport
                $rapport->update($updateData);
                $nbRapportsTraites++;

                $this->command->line("   ✅ Rapport ID {$rapport->id} - '{$rapport->intitule}' mis à jour");

            } catch (\Exception $e) {
                $this->command->line("   ❌ Erreur rapport ID {$rapport->id}: {$e->getMessage()}");
                Log::error('Erreur mise à jour rapport faisabilité', [
                    'rapport_id' => $rapport->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->command->info("✅ {$nbRapportsTraites} rapports de faisabilité mis à jour.");

        // Statistiques
        $totalRapportsFaisabilite = Rapport::where('type', 'faisabilite')->count();
        $this->command->info('📊 STATISTIQUES RAPPORTS FAISABILITÉ:');
        $this->command->line("   🔬 Total rapports faisabilité: {$totalRapportsFaisabilite}");
        $this->command->line("   ✅ Rapports traités: {$nbRapportsTraites}");
        $this->command->line("   📋 Canevas appliqués: " . count($canevas));

        if (!empty($canevasManquants)) {
            $this->command->line("   ⚠️ Canevas manquants: " . implode(', ', $canevasManquants));
        }
    }
}
