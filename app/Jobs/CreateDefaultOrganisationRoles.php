<?php

namespace App\Jobs;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\Permission;
use App\Repositories\DpafRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateDefaultOrganisationRoles implements ShouldQueue
{
    use Queueable;

    protected Organisation $organisation;

    /**
     * Create a new job instance.
     */
    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        try {
            // Définir les rôles de base à créer
            $defaultRoles = $this->getDefaultRoles();

            foreach ($defaultRoles as $roleData) {
                // Vérifier si le rôle existe déjà pour cette organisation
                $existingRole = Role::where('slug', $roleData['slug'])
                    ->where('roleable_type', Organisation::class)
                    ->where('roleable_id', $this->organisation->id)
                    ->first();

                if ($existingRole) {
                    Log::info("Role {$roleData['slug']} already exists for organisation {$this->organisation->id}");
                    continue;
                }

                // Créer le rôle pour cette organisation
                $role = Role::create([
                    'nom' => $roleData['nom'],
                    'slug' => $roleData['slug'],
                    'description' => $roleData['description'],
                    'roleable_id' => $this->organisation->id,
                    'roleable_type' => Organisation::class,
                ]);

                // Attacher les permissions au rôle
                if (!empty($roleData['permissions'])) {
                    $permissions = Permission::whereIn('slug', $roleData['permissions'])->pluck('id')->toArray();

                    if (!empty($permissions)) {
                        $role->permissions()->attach($permissions);
                    }
                }

                Log::info("Role {$roleData['slug']} created successfully for organisation {$this->organisation->id}");
            }

            $attributs = [
                "id_ministere" => $this->organisation->id,
                "nom" => "Direction de la Planification, de l'Administration et des Finances (DPAF)",
                "description" => "Direction administrative présente du {$this->organisation->nom}, chargée de la gestion des ressources humaines, financières et matérielles, ainsi que des services généraux au sein du ministère."
            ];

            app(DpafRepository::class)->getModel()->firstOrCreate(['id_ministere' => $attributs['id_ministere']], $attributs);

            DB::commit();

            Log::info("Default roles created successfully for organisation {$this->organisation->nom} (ID: {$this->organisation->id})");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Failed to create default roles for organisation {$this->organisation->id}: " . $e->getMessage(), [
                'organisation_id' => $this->organisation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Définir les rôles de base et leurs permissions
     */
    protected function getDefaultRoles(): array
    {

        return [
            [
                'nom' => 'DPAF',
                'slug' => 'dpaf',
                'description' => 'Directeur de la Planification, de l\'Administration et des Finances',
                'permissions' => [
                    // === A. GESTION ADMINISTRATIVE ===
                    "gerer-les-utilisateurs",
                    "voir-la-liste-des-utilisateurs",
                    "creer-un-utilisateur",
                    "modifier-un-utilisateur",
                    "supprimer-un-utilisateur",
                    "gerer-les-groupes-utilisateur",
                    "voir-la-liste-des-groupes-utilisateur",
                    "creer-un-groupe-utilisateur",
                    "modifier-un-groupe-utilisateur",
                    "supprimer-un-groupe-utilisateur",
                    "assigner-un-role-a-un-groupe-utilisateur",
                    "retirer-un-role-a-un-groupe-utilisateur",
                    "ajouter-un-utilisateur-a-un-groupe-utilisateur",
                    "ajouter-nouvel-utilisateur-a-un-groupe-utilisateur",
                    "gerer-les-roles",
                    "voir-la-liste-des-roles",
                    "creer-un-role",
                    "modifier-un-role",
                    "supprimer-un-role",
                    "assigner-des-permissions-a-un-role",
                    "retirer-des-permissions-a-un-role",

                    // === A. CONSULTATION ADMINISTRATIVE ===

                    // Consultation DGPD et départements
                    "voir-la-liste-des-departements",

                    // === B. GESTION DES ORGANISATIONS ===
                    "voir-la-liste-des-organisations",

                    // === C. CONSULTATION DES DONNÉES DE RÉFÉRENCE ===
                    "voir-la-liste-des-odds",
                    "voir-la-liste-des-cibles",
                    "voir-les-departements-geo",
                    "voir-la-liste-des-communes",
                    "voir-la-liste-des-arrondissements",
                    "voir-la-liste-des-villages",
                    "voir-la-liste-des-grands-secteurs",
                    "voir-la-liste-des-secteurs",
                    "voir-la-liste-des-sous-secteurs",
                    "voir-la-liste-des-types-intervention",
                    "voir-la-liste-des-categories-de-projet",

                    // Financements - Gestion complète (rôle DPAF)
                    "voir-la-liste-des-types-financement",
                    "voir-la-liste-des-natures-financement",
                    "voir-la-liste-des-sources-financement",
                    "gerer-les-financements",

                    // Programmes - Consultation
                    "voir-la-liste-des-programmes",
                    "voir-la-liste-des-composants-programme",

                    // Cadres stratégiques
                    "voir-la-liste-des-axes-du-pag",
                    "voir-la-liste-des-piliers-du-pag",
                    "voir-la-liste-des-actions-du-pag",
                    "voir-la-liste-des-orientations-strategique-du-pnd",
                    "voir-la-liste-des-objectifs-strategique-du-pnd",
                    "voir-la-liste-des-resultats-strategique-du-pnd",

                    // === D. CONSULTATION DES IDÉES DE PROJET ===
                    "voir-la-liste-des-idees-de-projet",
                    "consulter-une-idee-de-projet",
                    "exporter-une-idee-de-projet",
                    "imprimer-une-idee-de-projet",
                    "voir-les-commentaires-d-une-idee-de-projet",
                    "voir-les-documents-d-une-idee-de-projet",
                    "telecharger-les-documents-d-une-idee-de-projet",

                    // Commentaires sur les idées
                    "commenter-une-idee-de-projet",

                    // Gestion des projets
                    "suivre-avancement-projet",
                    "generer-rapport-projet",

                    "exporter-le-resultats-de-l-analyse-d-une-idee-de-projet",
                    "commenter-le-resultats-de-l-analyse-d-une-idee-de-projet",

                    "valider-une-idee-de-projet-en-interne",
                    "exporter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",
                    "commenter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",

                    "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",
                    "commenter-le-resultats-de-l-amc-d-une-idee-de-projet",


                    // Canevas et fiches
                    "consulter-le-canevas-de-la-fiche-idee-de-projet",
                    "telecharger-la-fiche-synthese-une-idee-de-projet",

                    // Grilles d'analyse - Consultation
                    "consulter-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                    "imprimer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                    "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet",
                    "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet",
                    "consulter-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",
                    "imprimer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",

                    // Tableaux de bord
                    "acceder-au-tableau-de-bord-de-pertinence",
                    "acceder-au-tableau-de-bord-climatique",
                    "acceder-au-tableau-d-amc",

                    // Projets - Consultation
                    "voir-la-liste-des-projets",
                    "consulter-un-projet",
                    "exporter-un-projet",
                    "imprimer-un-projet",
                    "voir-les-commentaires-d-un-projet",
                    "voir-les-documents-d-un-projet",
                    "telecharger-les-documents-d-un-projet",
                    "voir-historique-projet",

                    // === E. GESTION DES NOTES CONCEPTUELLES ===
                    "voir-la-liste-des-notes-conceptuelle",
                    "gerer-les-notes-conceptuelle",
                    "creer-une-note-conceptuelle",
                    "modifier-une-note-conceptuelle",
                    "supprimer-une-note-conceptuelle",
                    "voir-la-liste-des-commentaires-d-une-note-conceptuelle",
                    "imprimer-une-note-conceptuelle",
                    "voir-les-documents-relatifs-a-une-note-conceptuelle",
                    "telecharger-les-documents-relatifs-a-une-note-conceptuelle",
                    "consulter-la-fiche-de-redaction-d-une-note-conceptuelle",
                    "imprimer-la-fiche-de-redaction-d-une-note-conceptuelle",
                    "voir-le-resultats-d-evaluation-d-une-note-conceptuelle",
                    "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle",
                    "consulter-l-outil-d-analyse-d-une-note-conceptuelle",
                    "imprimer-l-outil-d-analyse-d-une-note-conceptuelle",

                    "rediger-une-note-conceptuelle",
                    "commenter-une-note-conceptuelle",
                    "televerser-une-note-conceptuelle",
                    "attacher-des-documents-relatifs-a-une-note-conceptuelle",

                    // === F. GESTION DES TDRs PRÉFAISABILITÉ ===
                    "voir-la-liste-des-tdrs-de-prefaisabilite",
                    "gerer-les-tdrs-de-prefaisabilite",
                    "creer-un-tdr-de-prefaisabilite",
                    "modifier-un-tdr-de-prefaisabilite",
                    "supprimer-un-tdr-de-prefaisabilite",
                    "soumettre-tdr-prefaisabilite",
                    "soumettre-un-tdr-de-prefaisabilite",
                    "consulter-le-details-d-appreciation-d-un-tdr-de-prefaisabilite",
                    "voir-details-de-l-appreciation-un-tdr-de-prefaisabilite",
                    "exporter-l-appreciation-d-un-tdr-de-prefaisabilite",
                    "commenter-l-appreciation-d-un-tdr-de-prefaisabilite",
                    "attacher-un-fichier-a-un-tdr-de-prefaisabilite",
                    "voir-tdr-prefaisabilite",
                    "telecharger-tdr-prefaisabilite",


                    // === G. GESTION ET SOUMISSION DES RAPPORTS PRÉFAISABILITÉ ===
                    "voir-la-liste-des-rapports-de-prefaisabilite",
                    "creer-un-rapport-de-prefaisabilite",
                    "modifier-un-rapport-de-prefaisabilite",
                    "supprimer-un-rapport-de-prefaisabilite",
                    "telecharger-un-rapport-de-prefaisabilite",
                    "soumettre-un-rapport-de-prefaisabilite",
                    "consulter-les-details-de-la-validation-de-l-etude-de-prefaisabilite",
                    "commenter-la-decision-de-validation-de-l-etude-de-prefaisabilite",
                    "gerer-les-rapports-de-prefaisabilite",
                    "voir-la-liste-des-rapports-de-faisabilite-preliminaire",
                    "gerer-les-rapports-de-faisabilite-preliminaire",
                    "creer-un-rapport-de-faisabilite-preliminaire",
                    "modifier-un-rapport-de-faisabilite-preliminaire",
                    "supprimer-un-rapport-de-faisabilite-preliminaire",
                    "telecharger-un-rapport-de-faisabilite-preliminaire",
                    "soumettre-un-rapport-de-faisabilite-preliminaire",
                    "commenter-un-rapport-de-faisabilite-preliminaire",

                    // === H. GESTION DES TDRs FAISABILITÉ ===
                    "voir-la-liste-des-tdrs-de-faisabilite",
                    "gerer-les-tdrs-de-faisabilite",
                    "creer-un-tdr-de-faisabilite",
                    "modifier-un-tdr-de-faisabilite",
                    "supprimer-un-tdr-de-faisabilite",
                    "soumettre-tdr-faisabilite",
                    "consulter-le-details-d-appreciation-d-un-tdr-de-faisabilite",
                    "voir-details-de-l-appreciation-un-tdr-de-faisabilite",
                    "exporter-l-appreciation-d-un-tdr-de-faisabilite",
                    "commenter-l-appreciation-d-un-tdr-de-faisabilite",
                    "attacher-un-fichier-a-un-tdr-de-faisabilite",
                    "voir-tdr-faisabilite",
                    "telecharger-tdr-faisabilite",

                    // === I. GESTION ET SOUMISSION DES RAPPORTS FAISABILITÉ ===
                    "voir-la-liste-des-rapports-de-faisabilite",
                    "creer-un-rapport-de-faisabilite",
                    "modifier-un-rapport-de-faisabilite",
                    "supprimer-un-rapport-de-faisabilite",
                    "telecharger-un-rapport-de-faisabilite",
                    "soumettre-un-rapport-de-faisabilite",
                    "consulter-les-details-de-la-validation-de-l-etude-de-faisabilite",
                    "commenter-la-decision-de-validation-de-l-etude-de-faisabilite",
                    "gerer-les-rapports-de-faisabilite",
                    "soumettre-un-tdr-de-faisabilite",


                    // === J. ÉVALUATION EX-ANTE - Validation financière ===
                    "voir-la-liste-des-rapports-d-evaluation-ex-ante",
                    "consulter-un-rapport-d-evaluation-ex-ante",
                    "telecharger-un-rapport-d-evaluation-ex-ante",
                    "imprimer-un-rapport-d-evaluation-ex-ante",
                    "exporter-un-rapport-d-evaluation-ex-ante",
                    "consulter-les-details-de-la-validation-de-la-validation-finale",
                    "commenter-la-decision-de-validation-finale",
                    "voir-les-documents-annexes-d-un-rapport-d-evaluation-ex-ante",
                    "telecharger-les-documents-annexes-d-un-rapport-d-evaluation-ex-ante",
                    "commenter-un-rapport-d-evaluation-ex-ante",
                    "voir-les-commentaires-d-un-rapport-d-evaluation-ex-ante",
                    "voir-historique-rapport-d-evaluation-ex-ante",
                    "soumettre-un-rapport-d-evaluation-ex-ante",
                    "modifier-un-rapport-d-evaluation-ex-ante",
                    "supprimer-un-rapport-d-evaluation-ex-ante",
                    "gerer-les-rapports-d-evaluation-ex-ante",
                    "attacher-un-document-annexe-a-un-rapport-d-evaluation-ex-ante",
                    "supprimer-un-document-annexe-d-un-rapport-d-evaluation-ex-ante",

                    // === K. CANEVAS ET OUTILS ===
                    "voir-la-liste-des-canevas",
                    "consulter-le-canevas-d-appreciation-d-un-tdr",
                    "imprimer-le-canevas-d-appreciation-d-un-tdr",
                    "voir-le-canevas-de-la-fiche-idee",
                    "telecharger-canevas-fiche-idee",

                    // === L. COMMENTAIRES ET FICHIERS ===
                    "ajouter-commentaire",
                    "voir-commentaires",
                    "modifier-commentaire",
                    "supprimer-commentaire",
                    "telecharger-fichier",
                    "consulter-un-fichier",
                    "telecharger-un-fichier",
                    "telecharger-documents",


                    // Gestion les notes conceptuelle
                    "commenter-l-appreciation-d-une-note-conceptuelle",
                    "consulter-les-details-de-la-validation-de-l-etude-de-profil",
                    "commenter-la-decision-de-validation-de-l-etude-de-profil",
                ]
            ],
            [
                'nom' => 'Responsable de Projet',
                'slug' => 'responsable-projet',
                'description' => 'Responsable de la gestion et du suivi des projets',
                'permissions' => [
                    // === CONSULTATION ADMINISTRATIVE ===
                    "voir-la-liste-des-utilisateurs",
                    "voir-la-liste-des-groupes-utilisateur",
                    "voir-la-liste-des-roles",
                    "voir-la-dpaf",
                    "voir-la-liste-des-departements",

                    // === CONSULTATION DES DONNÉES DE RÉFÉRENCE ===
                    "voir-la-liste-des-odds",
                    "voir-la-liste-des-cibles",
                    "voir-les-departements-geo",
                    "voir-la-liste-des-communes",
                    "voir-la-liste-des-arrondissements",
                    "voir-la-liste-des-villages",
                    "voir-la-liste-des-grands-secteurs",
                    "voir-la-liste-des-secteurs",
                    "voir-la-liste-des-sous-secteurs",
                    "voir-la-liste-des-types-intervention",
                    "voir-la-liste-des-types-financement",
                    "voir-la-liste-des-natures-financement",
                    "voir-la-liste-des-sources-financement",
                    "voir-la-liste-des-programmes",
                    "voir-la-liste-des-composants-programme",
                    "voir-la-liste-des-axes-du-pag",
                    "voir-la-liste-des-piliers-du-pag",
                    "voir-la-liste-des-actions-du-pag",
                    "voir-la-liste-des-orientations-strategique-du-pnd",
                    "voir-la-liste-des-objectifs-strategique-du-pnd",
                    "voir-la-liste-des-resultats-strategique-du-pnd",
                    "voir-la-liste-des-categories-de-projet",

                    // === GESTION DES IDÉES DE PROJET ===
                    "voir-la-liste-des-idees-de-projet",
                    "gerer-les-idees-projet",
                    "creer-une-idee-de-projet",
                    "modifier-une-idee-de-projet",
                    "supprimer-une-idee-de-projet",
                    "consulter-une-idee-de-projet",
                    "exporter-une-idee-de-projet",
                    "imprimer-une-idee-de-projet",
                    "commenter-une-idee-de-projet",
                    "voir-les-commentaires-d-une-idee-de-projet",
                    "voir-les-documents-d-une-idee-de-projet",
                    "telecharger-les-documents-d-une-idee-de-projet",

                    // Commentaires sur les idées
                    "commenter-une-idee-de-projet",



                    // Gestion des projets
                    "suivre-avancement-projet",
                    "generer-rapport-projet",


                    "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",
                    "commenter-le-resultats-de-l-amc-d-une-idee-de-projet",

                    // Évaluation climatique
                    "effectuer-evaluation-climatique-idee-projet",
                    "obtenir-score-climatique",
                    "obtenir-score-climatique-une-projet",
                    "valider-le-score-climatique-une-idee-de-projet",
                    "relancer-l-evaluation-climatique-une-idee-de-projet",

                    "exporter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",
                    "commenter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",

                    // Évaluation pertinence
                    "effectuer-evaluation-pertinence-idee-projet",
                    "acceder-au-tableau-de-bord-de-pertinence",
                    "exporter-le-resultats-de-l-analyse-d-une-idee-de-projet",
                    "commenter-le-resultats-de-l-analyse-d-une-idee-de-projet",
                    "valider-le-score-de-pertinence-d-une-idee-de-projet",
                    "relancer-l-evaluation-de-pertinence-d-une-idee-de-projet",


                    // AMC
                    "acceder-au-tableau-d-amc",
                    "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",
                    "commenter-le-resultats-de-l-amc-d-une-idee-de-projet",

                    // Canevas fiche idée
                    "consulter-le-canevas-de-la-fiche-idee-de-projet",
                    "remplir-le-canevas-de-la-fiche-idee-de-projet",
                    "telecharger-la-fiche-synthese-une-idee-de-projet",

                    // Grilles d'analyse - Consultation
                    "consulter-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                    "imprimer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                    "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet",
                    "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet",
                    "acceder-au-tableau-de-bord-climatique",
                    "consulter-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",
                    "imprimer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",

                    // === CONSULTATION DES PROJETS ===
                    "voir-la-liste-des-projets",
                    "consulter-un-projet",
                    "exporter-un-projet",
                    "imprimer-un-projet",
                    "commenter-un-projet",
                    "voir-les-commentaires-d-un-projet",
                    "voir-les-documents-d-un-projet",
                    "telecharger-les-documents-d-un-projet",
                    "suivre-avancement-projet",
                    "generer-rapport-projet",
                    "voir-historique-projet",

                    // === CONSULTATION DES NOTES CONCEPTUELLES ===
                    "voir-la-liste-des-notes-conceptuelle",
                    "commenter-une-note-conceptuelle",
                    "voir-la-liste-des-commentaires-d-une-note-conceptuelle",
                    "imprimer-une-note-conceptuelle",
                    "voir-les-documents-relatifs-a-une-note-conceptuelle",
                    "telecharger-les-documents-relatifs-a-une-note-conceptuelle",
                    "consulter-la-fiche-de-redaction-d-une-note-conceptuelle",
                    "imprimer-la-fiche-de-redaction-d-une-note-conceptuelle",
                    "voir-le-resultats-d-evaluation-d-une-note-conceptuelle",
                    "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle",
                    "consulter-l-outil-d-analyse-d-une-note-conceptuelle",
                    "imprimer-l-outil-d-analyse-d-une-note-conceptuelle",
                    "commenter-l-appreciation-d-une-note-conceptuelle",
                    "consulter-les-details-de-la-validation-de-l-etude-de-profil",
                    "commenter-la-decision-de-validation-de-l-etude-de-profil",

                    // === CONSULTATION DES TDRs PRÉFAISABILITÉ ===
                    "voir-la-liste-des-tdrs-de-prefaisabilite",
                    "voir-tdr-prefaisabilite",
                    "telecharger-tdr-prefaisabilite",
                    "attacher-un-fichier-a-un-tdr-de-prefaisabilite",
                    "consulter-le-details-d-appreciation-d-un-tdr-de-prefaisabilite",
                    "voir-details-de-l-appreciation-un-tdr-de-prefaisabilite",
                    "exporter-l-appreciation-d-un-tdr-de-prefaisabilite",
                    "commenter-l-appreciation-d-un-tdr-de-prefaisabilite",

                    // === CONSULTATION ET GESTION DES RAPPORTS PRÉFAISABILITÉ ===
                    "voir-la-liste-des-rapports-de-prefaisabilite",
                    "telecharger-un-rapport-de-prefaisabilite",
                    "consulter-les-details-de-la-validation-de-l-etude-de-prefaisabilite",
                    "commenter-la-decision-de-validation-de-l-etude-de-prefaisabilite",

                    // === CONSULTATION DES RAPPORTS FAISABILITÉ PRÉLIMINAIRE ===
                    "voir-la-liste-des-rapports-de-faisabilite-preliminaire",
                    "telecharger-un-rapport-de-faisabilite-preliminaire",
                    "commenter-un-rapport-de-faisabilite-preliminaire",

                    // === CONSULTATION DES TDRs FAISABILITÉ ===
                    "voir-la-liste-des-tdrs-de-faisabilite",
                    "voir-tdr-faisabilite",
                    "telecharger-tdr-faisabilite",
                    "attacher-un-fichier-a-un-tdr-de-faisabilite",
                    "consulter-le-details-d-appreciation-d-un-tdr-de-faisabilite",
                    "voir-details-de-l-appreciation-un-tdr-de-faisabilite",
                    "exporter-l-appreciation-d-un-tdr-de-faisabilite",
                    "commenter-l-appreciation-d-un-tdr-de-faisabilite",

                    // === GESTION DES RAPPORTS FAISABILITÉ ===
                    "voir-la-liste-des-rapports-de-faisabilite",
                    "telecharger-un-rapport-de-faisabilite",
                    "consulter-les-details-de-la-validation-de-l-etude-de-faisabilite",
                    "commenter-la-decision-de-validation-de-l-etude-de-faisabilite",

                    // === G. GESTION ET SOUMISSION DES RAPPORTS PRÉFAISABILITÉ ===

                    // === CONSULTATION DES RAPPORTS ÉVALUATION EX-ANTE ===
                    "voir-la-liste-des-rapports-d-evaluation-ex-ante",
                    "consulter-un-rapport-d-evaluation-ex-ante",
                    "telecharger-un-rapport-d-evaluation-ex-ante",
                    "imprimer-un-rapport-d-evaluation-ex-ante",
                    "exporter-un-rapport-d-evaluation-ex-ante",
                    "consulter-les-details-de-la-validation-de-la-validation-finale",
                    "commenter-la-decision-de-validation-finale",
                    "voir-les-documents-annexes-d-un-rapport-d-evaluation-ex-ante",
                    "telecharger-les-documents-annexes-d-un-rapport-d-evaluation-ex-ante",
                    "commenter-un-rapport-d-evaluation-ex-ante",
                    "voir-les-commentaires-d-un-rapport-d-evaluation-ex-ante",
                    "voir-historique-rapport-d-evaluation-ex-ante",

                    // === CANEVAS ET OUTILS - Consultation ===
                    "voir-la-liste-des-canevas",
                    "consulter-le-canevas-d-appreciation-d-un-tdr",
                    "imprimer-le-canevas-d-appreciation-d-un-tdr",
                    "voir-le-canevas-de-la-fiche-idee",
                    "telecharger-canevas-fiche-idee",

                    // === COMMENTAIRES ET FICHIERS GÉNÉRAUX ===
                    "ajouter-commentaire",
                    "voir-commentaires",
                    "modifier-commentaire",
                    "supprimer-commentaire",
                    "telecharger-fichier",
                    "upload-fichier",
                    "supprimer-fichier",
                    "consulter-un-fichier",
                    "telecharger-un-fichier",
                    "telecharger-documents"
                ]
            ]
        ];
    }
}
