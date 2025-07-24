<?php

namespace Database\Seeders;

use App\Traits\ForeignKeyConstraints;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    use ForeignKeyConstraints;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //$this->disableForeignKeyChecks();
        // Supprimer les anciens rôles et créer les nouveaux
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        //$this->enableForeignKeyChecks();

        $espaces = ["administration-general", "dpaf", "dgpb", "dgb", "ministere", "institution"];

        $roles_par_espace = [
            "administration-general" => ["Super Admin"],
            "dpaf" => ["Responsable Projet", "Responsable Hierachique"],
            "dgpb" => ["Responsable DGPB", "Analyste DGPD"]/*
            "ministere" => ["Ministere"],
            "institution" => ["Président Institution", "Secrétaire Exécutif", "Coordonnateur Projet"]*/
        ];

        $permissions_base = [
            // Gestion des utilisateurs
            "gerer-utilisateurs", "voir-utilisateurs", "creer-utilisateur", "modifier-utilisateur", "supprimer-utilisateur",

            // Gestion des rôles et permissions
            "gerer-roles", "voir-roles", "creer-role", "modifier-role", "supprimer-role", "assigner-permissions",

            // Gestion les odds
            "gerer-odds", "voir-odds", "creer-odd", "modifier-odd", "supprimer-odd",

            // Gestion les cibles
            "gerer-cibles", "voir-cibles", "creer-cible", "modifier-cible", "supprimer-cible",

            // Entités géographiques
            "voir-departements", "gerer-departements", "voir-communes", "gerer-communes",
            "voir-arrondissements", "gerer-arrondissements", "voir-villages", "gerer-villages",
            "recevoir-une-notification-validation-idee-projet",
            "recevoir-une-notification-resultat-idee",

            // Secteurs d'intervention
            "voir-grands-secteurs", "voir-secteurs", "gerer-secteurs",
            "voir-sous-secteurs", "voir-types-intervention", "gerer-types-intervention",

            // Financements
            "voir-types-financement", "voir-natures-financement", "voir-sources-financement", "gerer-financement",

            // Cadres stratégiques
            "voir-axes-pag", "voir-piliers-pag", "voir-actions-pag", "voir-orientations-pnd", "voir-objectifs-pnd", "voir-resultats-pnd",

            // Projets et idées
            "voir-categories-projet", "gerer-categories-projet", "voir-types-programme", "gerer-types-programme",
            "voir-composants-programme", "gerer-composants-programme", "voir-idees-projet", "gerer-idees-projet",
            "creer-idee-projet", "modifier-idee-projet", "supprimer-idee-projet", "valider-idee-projet",

            // Documents et TDR
            "voir-documents", "telecharger-canevas-analyse-idee", "modifier-canevas-analyse-idee", "voir-canevas-fiche-idee", "modifier-canevas-fiche-idee", "telecharger-canevas-fiche-idee", "modifier-canevas-grille-evaluation-climatique", "modifier-canevas-grille-evaluation-amc", "modifier-canevas-note-idee", "gerer-documents", "telecharger-documents", "creer-tdr", "modifier-tdr", "obtenir-score-climatique",
            "voir-tdr-prefaisabilite", "voir-tdr-faisabilite", "telecharger-tdr-prefaisabilite",
            "telecharger-tdr-faisabilite", "soumettre-tdr-faisabilite", "soumettre-tdr-prefaisabilite",

            // Notes conceptuelles
            "rediger-note-conception", "voir-note-conception", "modifier-note-conception",
            "evaluer-note-conception", "valider-note-conception", "approuver-note-conception", "recevoir-une-notification-nouvelle-idee-projet",

            // Évaluations et validations
            "voir-evaluations", "creer-evaluation", "modifier-evaluation", "soumettre-evaluation",
            "apprecier-tdr-faisabilite", "valider-tdr-faisabilite", "apprecier-tdr-prefaisabilite",
            "valider-tdr-prefaisabilite", "valider-etude-faisabilite", "valider-etude-prefaisabilite",

            // Rapports d'étude
            "soumettre-rapport-faisabilite", "soumettre-rapport-prefaisabilite", "voir-rapports-etude",
            "valider-rapport-faisabilite", "valider-rapport-prefaisabilite",

            // Workflow et suivi
            "voir-workflows", "gerer-workflows", "suivre-progression", "generer-rapports",

            // Commentaires et fichiers
            "ajouter-commentaire", "voir-commentaires", "modifier-commentaire", "supprimer-commentaire",
            "telecharger-fichier", "upload-fichier", "supprimer-fichier",

            // Fiches et synthèses
            "fiche-synthese-idee-projet", "generer-fiche-synthese", "exporter-donnees",

            // Administration système
            "configuration-systeme", "gestion-logs", "maintenance-systeme", "backup-donnees"
        ];

        $permissions_par_role = [
            // Administration Générale
            "Super Admin" => $permissions_base,

            // DPAF
            "Responsable Projet" => [
                "voir-idees-projet","voir-details-idee-projet", "gerer-idees-projet", "voir-evaluations", "voir-canevas-fiche-idee",
                "remplir-canevas-idee-projet", "modifier-une-idee-projet", "supprimer-une-idee-projet", "creer-une-idee-projet", "obtenir-score-climatique",
                "recevoir-notification-resultats-validation-idee",
                "voir-axes-pag", "voir-piliers-pag", "voir-actions-pag", "voir-orientations-pnd", "voir-objectifs-pnd", "voir-resultats-pnd", "voir-types-financement", "voir-sources-financement", "voir-sources-financement",

                "creer-evaluation", "voir-cible", "voir-odd", "voir-grands-secteurs", "voir-secteurs", "voir-sous-secteurs"
            ],
            "Responsable Hierachique" => [
                "voir-idees-projet", "consulter-une-fiche-synthese-idee", "telecharger-une-fiche-synthese-idee", "valider-idee-projet", "emettre-commentaire", "attacher-fichier", "voir-fichier", "partager-fichier",
                "recevoir-une-notification-nouvelle-idee-projet", "transferer-idee-projet",
            ],
            "Analyste DGPD" => [
                "voir-idees-projet", "voir-grille-evaluation-amc",
                "remplir-grille-amc", "enregistrer-fiche-synthese-amc", "modifier-fiche-synthese-amc", "emettre-commentaire", "rejeter-idee-idee", "recevoir-notification-validation-idee", "voir-historique-amc", "telecharger-fiche-synthese-amc",
                "voir-types-financement", "voir-sources-financement", "apprecier-tdr-faisabilite",
                "soumettre-rapport-faisabilite", "voir-rapports-etude"
            ],
            "Responsable DGPD" => [
                "voir-idees-projet", "voir-grille-evaluation-amc",
                "remplir-grille-amc", "enregistrer-fiche-synthese-amc", "modifier-fiche-synthese-amc", "emettre-commentaire", "rejeter-idee-idee", "recevoir-notification-validation-idee", "voir-historique-amc", "telecharger-fiche-synthese-amc",
                "voir-types-financement", "voir-sources-financement", "apprecier-tdr-faisabilite",
                "soumettre-rapport-faisabilite", "voir-rapports-etude"
            ],


            // DPAF
            "DPAF" => [

                "voir-idees-projet", "gerer-idees-projet", "voir-evaluations", "voir-canevas-fiche-idee",
                "remplir-canevas-idee-projet", "creer-une-note-projet", "obtenir-score-climatique",
                "recevoir-notification-resultats-validation-idee",
                "voir-axes-pag", "voir-piliers-pag", "voir-actions-pag", "voir-orientations-pnd", "voir-objectifs-pnd", "voir-resultats-pnd", "voir-types-financement", "voir-sources-financement", "voir-sources-financement",

                "creer-evaluation", "voir-cible", "voir-odd", "voir-grands-secteurs", "voir-secteurs", "voir-sous-secteurs",

                "voir-notes-conceptuelle", "rediger-note-conceptuelle", "consulter-canevas-redaction-note-conceptuelle", "voir-commentaires-note-conceptuelle", "telecharger-note-conceptuelle", "televerser-note-conceptuelle", "attacher-fichier-note-conceptuelle", "enregistrer-une-note-conceptuelle", "transferer-une-note-conceptuelle", "evaluer-note-conception",
                "voir-details-idee-projet","voir-projets","voir-details-projets"
            ]

        ];

        $groupes_utilisateurs = [
            "Coordination ministérielle", "Cellule planification" => [
                "recevoir-notification-demande-validation-note-conceptuelle",
            ]
        ];

        // Créer les permissions
        foreach ($permissions_base as $permission) {
            \App\Models\Permission::firstOrCreate([
                'slug' => $permission
            ], [
                'nom' => ucfirst(str_replace('-', ' ', $permission)),
                'description' => 'Permission pour ' . str_replace('-', ' ', $permission)
            ]);
        }

        // Créer les rôles et associer les permissions
        foreach ($espaces as $espace) {
            if (isset($roles_par_espace[$espace])) {
                foreach ($roles_par_espace[$espace] as $role_name) {
                    $role = \App\Models\Role::firstOrCreate([
                        'slug' => strtolower(str_replace(' ', '-', $role_name . '-' . $espace))
                    ], [
                        'nom' => $role_name,
                        'description' => "Rôle {$role_name} pour l'espace {$espace}",
                    ]);

                    // Associer les permissions au rôle
                    if (isset($permissions_par_role[$role_name])) {
                        $permissions = \App\Models\Permission::whereIn('slug', $permissions_par_role[$role_name])->get();
                        $role->permissions()->sync($permissions->pluck('id')->toArray());
                    }
                }
            }
        }
    }
}