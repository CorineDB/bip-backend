<?php

namespace Database\Seeders;

use App\Models\Personne;
use App\Models\Role;
use App\Models\User;
use App\Traits\ForeignKeyConstraints;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        $espaces = ["administration-general", "dpaf", "dgpb", "organisation"];

        $roles_par_espace = [
            "administration-general" => ["Super Admin"],
            "dpaf" => ["DPAF"],
            "dgpb" => ["DGPD"],
            "organisation" => ["Organisation"]
        ];

        $permissions_base = [
            // Gestion des utilisateurs
            "gerer-les-utilisateurs", "voir-la-liste-des-utilisateurs", "creer-un-utilisateur", "modifier-un-utilisateur", "supprimer-un-utilisateur",

            // Gestion des groupes-utilisateur
            "gerer-les-groupes-utilisateur", "voir-la-liste-des-groupes-utilisateur", "creer-un-groupe-utilisateur", "modifier-un-groupe-utilisateur", "supprimer-un-groupe-utilisateur", "assigner-un-role-a-un-groupe-utilisateur", "retirer-un-role-a-un-groupe-utilisateur", "ajouter-un-utilisateur-a-un-groupe-utilisateur", "ajouter-nouvel-utilisateur-a-un-groupe-utilisateur",

            // Gestion des rôles et permissions
            "gerer-les-roles", "voir-la-liste-des-roles", "creer-un-role", "modifier-un-role", "supprimer-un-role", "assigner-des-permissions-a-un-role", "retirer-des-permissions-a-un-role",


            "gerer-la-dpaf", "voir-la-dpaf", "creer-la-dpaf", "modifier-la-dpaf", "supprimer-la-dpaf",

            "gerer-les-departements", "voir-la-liste-des-departements", "creer-un-departement", "modifier-un-departement", "supprimer-un-departement",

            "gerer-la-dgpd", "voir-la-dgpd", "creer-la-dgpd", "modifier-la-dgpd", "supprimer-la-dgpd",

            // Gestion les odds
            "gerer-les-odds", "voir-la-liste-des-odds", "creer-un-odd", "modifier-un-odd", "supprimer-un-odd",

            // Gestion les cibles
            "gerer-les-cibles", "voir-la-liste-des-cibles", "creer-une-cible", "modifier-une-cible", "supprimer-une-cible",

            // Entités géographiques
            "voir-les-departements-geo", "gerer-les-departements-geo", "voir-la-liste-des-communes", "gerer-les-communes",
            "voir-la-liste-des-arrondissements", "gerer-les-arrondissements", "voir-la-liste-des-villages", "gerer-les-villages",

            // Secteurs d'intervention
            "voir-la-liste-des-grands-secteurs", "voir-la-liste-des-secteurs", "voir-la-liste-des-sous-secteurs", "gerer-les-secteurs", "creer-un-secteur", "modifier-un-secteur", "supprimer-un-secteur",

            "voir-la-liste-des-types-intervention", "gerer-les-types-intervention", "creer-un-type-intervention", "modifier-un-type-intervention", "supprimer-un-type-intervention",

            // Financements
            "voir-la-liste-des-types-financement", "voir-la-liste-des-natures-financement", "voir-la-liste-des-sources-financement", "gerer-les-financements", "creer-un-financement", "modifier-un-financement", "supprimer-un-financement",

            // Programmes
            "voir-la-liste-des-programmes", "voir-la-liste-des-composants-programme", "gerer-un-programme", "creer-un-programme", "modifier-un-programme", "supprimer-un-programme", "gerer-les-composants-de-programme", "creer-un-composant-de-programme", "modifier-un-composant-de-programme", "supprimer-un-composant-de-programme",

            // Cadres stratégiques
            "voir-la-liste-des-axes-du-pag", "voir-la-liste-des-piliers-du-pag", "voir-la-liste-des-actions-du-pag", "voir-la-liste-des-orientations-strategique-du-pnd", "voir-la-liste-des-objectifs-strategique-du-pnd", "voir-la-liste-des-resultats-strategique-du-pnd",

            // Projets et idées
            "voir-la-liste-des-categories-de-projet", "gerer-les-categories-de-projet", "creer-une-categorie-de-projet", "modifier-une-categorie-de-projet", "supprimer-une-categorie-de-projet",
            "voir-la-liste-des-idees-de-projet", "gerer-les-idees-projet", "creer-une-idee-de-projet", "modifier-une-idee-de-projet", "supprimer-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "obtenir-score-climatique", "obtenir-score-climatique-une-projet", "valider-le-score-climatique-une-idee-de-projet", "relancer-l-evaluation-climatique-une-idee-de-projet",

            "creer-le-canevas-de-la-fiche-idee-de-projet", "modifier-le-canevas-de-la-fiche-idee-de-projet", "consulter-le-canevas-de-la-fiche-idee-de-projet", "remplir-le-canevas-de-la-fiche-idee-de-projet", "telecharger-la-fiche-synthese-une-idee-de-projet",

            "creer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet", "modifier-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet", "consulter-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet", "effectuer-evaluation-pertinence-idee-projet", "valider-le-score-de-pertinence-d-une-idee-de-projet", "relancer-l-evaluation-de-pertinence-d-une-idee-de-projet", "acceder-au-tableau-de-bord-de-pertinence", "exporter-le-resultats-de-l-analyse-d-une-idee-de-projet", "commenter-le-resultats-de-l-analyse-d-une-idee-de-projet",

            "creer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "modifier-la-grille-d-analyse-climatique-d-une-idee-de-projet", "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "acceder-au-tableau-de-bord-climatique", "valider-le-score-d-analyse-climatique-interne-d-une-idee-de-projet", "valider-une-idee-de-projet-en-interne", "exporter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet", "commenter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",

            "creer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet", "modifier-la-grille-d-analyse-multi-critere-d-une-idee-de-projet", "consulter-la-grille-d-analyse-multi-critere-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet", "effectuer-l-analyse-climatique-d-une-idee-de-projet", "acceder-au-tableau-d-amc", "effectuer-l-amc-d-une-idee-de-projet", "valider-une-idee-de-projet-a-projet", "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet", "commenter-le-resultats-de-l-amc-d-une-idee-de-projet",

            // Gestion les notes conceptuelle
            "gerer-les-notes-conceptuelle", "voir-la-liste-des-notes-conceptuelle", "creer-une-note-conceptuelle", "rediger-une-note-conceptuelle", "modifier-une-note-conceptuelle", "supprimer-une-note-conceptuelle", "commenter-une-note-conceptuelle", "voir-la-liste-des-commentaires-d-une-note-conceptuelle", "imprimer-une-note-conceptuelle", "televerser-une-note-conceptuelle", "attacher-des-documents-relatifs-a-une-note-conceptuelle", "voir-les-documents-relatifs-a-une-note-conceptuelle", "telecharger-les-documents-relatifs-a-une-note-conceptuelle",

            "creer-la-fiche-de-redaction-d-une-note-conceptuelle", "modifier-la-fiche-de-redaction-d-une-note-conceptuelle", "consulter-la-fiche-de-redaction-d-une-note-conceptuelle", "imprimer-la-fiche-de-redaction-d-une-note-conceptuelle", "evaluer-une-note-conceptulle", "voir-le-resultats-d-evaluation-d-une-note-conceptuelle", "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle", "valider-l-etude-de-profil",

            "creer-l-outil-d-analyse-d-une-note-conceptuelle", "modifier-l-outil-d-analyse-d-une-note-conceptuelle", "consulter-l-outil-d-analyse-d-une-note-conceptuelle", "imprimer-l-outil-d-analyse-d-une-note-conceptuelle",

            // FAISABILITE PRELIMINAIRE
            "soumettre-un-rapport-de-faisabilite-preliminaire","modifier-un-rapport-de-faisabilite-preliminaire", "supprimer-un-rapport-de-faisabilite-preliminaire", "telecharger-un-rapport-de-faisabilite-preliminaire", "commenter-un-rapport-de-faisabilite-preliminaire", "valider-une-etude-de-prefaisabilite", "gerer-les-rapports-de-faisabilite-preliminaire", "voir-la-liste-des-rapports-de-faisabilite-preliminaire",


            // TDRs
            "soumettre-un-tdr-de-prefaisabilite", "voir-la-liste-des-tdrs-de-prefaisabilite", "attacher-un-fichier-a-un-tdr-de-prefaisabilite", "supprimer-un-tdr-de-prefaisabilite", "apprecier-un-tdr-de-prefaisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-prefaisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-prefaisabilite", "voir-details-de-l-appreciation-un-tdr-de-prefaisabilite", "exporter-l-appreciation-d-un-tdr-de-prefaisabilite", "commenter-l-appreciation-d-un-tdr-de-prefaisabilite",
            "soumettre-un-rapport-de-prefaisabilite","modifier-un-rapport-de-prefaisabilite", "supprimer-un-rapport-de-prefaisabilite", "telecharger-un-rapport-de-prefaisabilite", "valider-une-etude-de-prefaisabilite", "gerer-les-rapports-de-prefaisabilite", "voir-la-liste-des-rapports-de-prefaisabilite",

            "soumettre-un-tdr-de-faisabilite", "voir-la-liste-des-tdrs-de-faisabilite", "attacher-un-fichier-a-un-tdr-de-faisabilite", "supprimer-un-tdr-de-faisabilite", "apprecier-un-tdr-de-faisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-faisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-faisabilite",
            "soumettre-un-rapport-de-faisabilite","modifier-un-rapport-de-faisabilite", "supprimer-un-rapport-de-faisabilite", "telecharger-un-rapport-de-faisabilite", "valider-une-etude-de-faisabilite", "gerer-les-rapports-de-faisabilite", "voir-la-liste-des-rapports-de-faisabilite",

            "soumettre-un-rapport-d-evaluation-ex-ante","modifier-un-rapport-d-evaluation-ex-ante", "supprimer-un-rapport-d-evaluation-ex-ante", "telecharger-un-rapport-d-evaluation-ex-ante", "valider-un-rapport-evaluation-ex-ante", "gerer-les-rapports-d-evaluation-ex-ante", "voir-la-liste-des-rapports-d-evaluation-ex-ante", "attacher-un-document-annexe-a-un-rapport-d-evaluation-ex-ante",

            "creer-le-canevas-d-appreciation-d-un-tdr", "modifier-le-canevas-d-appreciation-d-un-tdr", "consulter-le-canevas-d-appreciation-d-un-tdr", "imprimer-le-canevas-d-appreciation-d-un-tdr",

            // Documents et TDR
            "voir-la-liste-des-canevas", "gerer-les-canevas", "creer-un-canevas", "modifier-un-canevas", "supprimer-un-canevas", "imprimer-un-canevas",

            "voir-la-liste-des-canevas", "telecharger-un-canevas-analyse-idee", "modifier-canevas-analyse-idee", "voir-le-canevas-de-la-fiche-idee", "modifier-le-canevas-de-la-fiche-idee", "telecharger-canevas-fiche-idee", "modifier-canevas-grille-evaluation-climatique", "modifier-canevas-grille-evaluation-amc", "modifier-canevas-note-idee", "gerer-documents", "telecharger-documents",
            "creer-un-tdr", "modifier-un-tdr",
            "voir-tdr-prefaisabilite", "voir-tdr-faisabilite", "telecharger-tdr-prefaisabilite",
            "telecharger-tdr-faisabilite", "soumettre-tdr-faisabilite", "soumettre-tdr-prefaisabilite",

            // Évaluations et validations


            // Commentaires et fichiers
            "ajouter-commentaire", "voir-commentaires", "modifier-commentaire", "supprimer-commentaire",
            "telecharger-fichier", "upload-fichier", "supprimer-fichier",
        ];

        $permissions_par_role = [
            // Administration Générale
            "Super Admin" => $permissions_base,

            // DPAF
            "Responsable Projet" => [
                "voir-idees-projet","voir-details-idee-projet", "gerer-idees-projet", "voir-evaluations", "voir-canevas-fiche-idee",
                "remplir-canevas-idee-projet", "modifier-une-idee-projet", "supprimer-une-idee-projet", "creer-une-idee-projet", "obtenir-score-climatique",
                "recevoir-notification-resultats-validation-idee", "effectuer-evaluation-climatique-idee-projet",
                "voir-axes-pag", "voir-piliers-pag", "voir-actions-pag", "voir-orientations-pnd", "voir-objectifs-pnd", "voir-resultats-pnd", "voir-types-financement", "voir-sources-financement", "voir-sources-financement",

                "creer-evaluation", "voir-cible", "voir-odd", "voir-grands-secteurs", "voir-secteurs", "voir-sous-secteurs"
            ],
            "Organisation" => [
                // Gestion des utilisateurs
                "gerer-les-utilisateurs", "voir-la-liste-des-utilisateurs", "creer-un-utilisateur", "modifier-un-utilisateur", "supprimer-un-utilisateur",

                // Gestion des groupes-utilisateur
                "gerer-les-groupes-utilisateur", "voir-la-liste-des-groupes-utilisateur", "creer-un-groupe-utilisateur", "modifier-un-groupe-utilisateur", "supprimer-un-groupe-utilisateur", "assigner-un-role-a-un-groupe-utilisateur", "retirer-un-role-a-un-groupe-utilisateur", "ajouter-un-utilisateur-a-un-groupe-utilisateur", "ajouter-nouvel-utilisateur-a-un-groupe-utilisateur",

                // Gestion des rôles et permissions
                "gerer-les-roles", "voir-la-liste-des-roles", "creer-un-role", "modifier-un-role", "supprimer-un-role", "assigner-des-permissions-a-un-role", "retirer-des-permissions-a-un-role",

                "gerer-la-dpaf", "voir-la-dpaf", "creer-la-dpaf", "modifier-la-dpaf", "supprimer-la-dpaf",

                // Gestion les odds
                "gerer-les-odds",

                // Gestion les cibles
                "gerer-les-cibles",

                "gerer-les-departements", "voir-la-liste-des-departements", "creer-un-departement", "modifier-un-departement", "supprimer-un-departement",

                // Entités géographiques
                "voir-les-departements-geo", "voir-la-liste-des-communes", "voir-la-liste-des-arrondissements", "voir-la-liste-des-villages",

                // Secteurs d'intervention
                "voir-la-liste-des-grands-secteurs", "voir-la-liste-des-secteurs", "voir-la-liste-des-sous-secteurs",

                "voir-la-liste-des-types-intervention",

                // Financements
                "voir-la-liste-des-types-financement", "voir-la-liste-des-natures-financement", "voir-la-liste-des-sources-financement",

                // Programmes
                "voir-la-liste-des-programmes", "voir-la-liste-des-composants-programme",

                // Cadres stratégiques
                "voir-la-liste-des-axes-du-pag", "voir-la-liste-des-piliers-du-pag", "voir-la-liste-des-actions-du-pag", "voir-la-liste-des-orientations-strategique-du-pnd", "voir-la-liste-des-objectifs-strategique-du-pnd", "voir-la-liste-des-resultats-strategique-du-pnd",

                // Projets et idées
                "voir-la-liste-des-categories-de-projet",
                "voir-la-liste-des-idees-de-projet", "gerer-les-idees-projet", "creer-une-idee-de-projet", "modifier-une-idee-de-projet", "supprimer-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "obtenir-score-climatique", "obtenir-score-climatique-une-projet", "valider-le-score-climatique-une-idee-de-projet", "relancer-l-evaluation-climatique-une-idee-de-projet",

                "consulter-le-canevas-de-la-fiche-idee-de-projet", "remplir-le-canevas-de-la-fiche-idee-de-projet", "telecharger-la-fiche-synthese-une-idee-de-projet",

                "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "acceder-au-tableau-de-bord-climatique", "valider-le-score-d-analyse-climatique-interne-d-une-idee-de-projet", "valider-une-idee-de-projet-en-interne",

                "acceder-au-tableau-d-amc", "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",

                // Gestion les notes conceptuelle
                "gerer-les-notes-conceptuelle", "voir-la-liste-des-notes-conceptuelle", "creer-une-note-conceptuelle", "rediger-une-note-conceptuelle", "modifier-une-note-conceptuelle", "supprimer-une-note-conceptuelle", "commenter-une-note-conceptuelle", "voir-la-liste-des-commentaires-d-une-note-conceptuelle", "imprimer-une-note-conceptuelle", "televerser-une-note-conceptuelle", "attacher-des-documents-relatifs-a-une-note-conceptuelle", "voir-les-documents-relatifs-a-une-note-conceptuelle", "telecharger-les-documents-relatifs-a-une-note-conceptuelle",

                "consulter-la-fiche-de-redaction-d-une-note-conceptuelle", "imprimer-la-fiche-de-redaction-d-une-note-conceptuelle", "evaluer-une-note-conceptulle", "voir-le-resultats-d-evaluation-d-une-note-conceptuelle", "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle", "valider-l-etude-de-profil",

                "consulter-l-outil-d-analyse-d-une-note-conceptuelle", "imprimer-l-outil-d-analyse-d-une-note-conceptuelle",

                // TDRs
                "soumettre-un-tdr-de-prefaisabilite", "voir-la-liste-des-tdrs-de-prefaisabilite", "attacher-un-fichier-a-un-tdr-de-prefaisabilite", "supprimer-un-tdr-de-prefaisabilite", "apprecier-un-tdr-de-prefaisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-prefaisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-prefaisabilite",
                "soumettre-un-rapport-de-prefaisabilite","modifier-un-rapport-de-prefaisabilite", "supprimer-un-rapport-de-prefaisabilite", "telecharger-un-rapport-de-prefaisabilite", "valider-une-etude-de-prefaisabilite", "gerer-les-rapports-de-prefaisabilite", "voir-la-liste-des-rapports-de-prefaisabilite",

                "soumettre-un-tdr-de-faisabilite", "voir-la-liste-des-tdrs-de-faisabilite", "attacher-un-fichier-a-un-tdr-de-faisabilite", "supprimer-un-tdr-de-faisabilite", "apprecier-un-tdr-de-faisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-faisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-faisabilite",
                "soumettre-un-rapport-de-faisabilite","modifier-un-rapport-de-faisabilite", "supprimer-un-rapport-de-faisabilite", "telecharger-un-rapport-de-faisabilite", "valider-une-etude-de-faisabilite", "gerer-les-rapports-de-faisabilite", "voir-la-liste-des-rapports-de-faisabilite",

                "soumettre-un-rapport-d-evaluation-ex-ante","modifier-un-rapport-d-evaluation-ex-ante", "supprimer-un-rapport-d-evaluation-ex-ante", "telecharger-un-rapport-d-evaluation-ex-ante", "valider-un-rapport-evaluation-ex-ante", "gerer-les-rapports-d-evaluation-ex-ante", "voir-la-liste-des-rapports-d-evaluation-ex-ante", "attacher-un-document-annexe-a-un-rapport-d-evaluation-ex-ante",

                "consulter-le-canevas-d-appreciation-d-un-tdr", "imprimer-le-canevas-d-appreciation-d-un-tdr",

                // Commentaires et fichiers
                "ajouter-commentaire", "voir-commentaires", "modifier-commentaire", "supprimer-commentaire",
                "telecharger-fichier", "upload-fichier", "supprimer-fichier",
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

            "DGPD" => [
                // Gestion des utilisateurs
                "gerer-les-utilisateurs", "voir-la-liste-des-utilisateurs", "creer-un-utilisateur", "modifier-un-utilisateur", "supprimer-un-utilisateur",

                // Gestion des groupes-utilisateur
                "gerer-les-groupes-utilisateur", "voir-la-liste-des-groupes-utilisateur", "creer-un-groupe-utilisateur", "modifier-un-groupe-utilisateur", "supprimer-un-groupe-utilisateur", "assigner-un-role-a-un-groupe-utilisateur", "retirer-un-role-a-un-groupe-utilisateur", "ajouter-un-utilisateur-a-un-groupe-utilisateur", "ajouter-nouvel-utilisateur-a-un-groupe-utilisateur",

                // Gestion des rôles et permissions
                "gerer-les-roles", "voir-la-liste-des-roles", "creer-un-role", "modifier-un-role", "supprimer-un-role", "assigner-des-permissions-a-un-role", "retirer-des-permissions-a-un-role",

                // Gestion les odds
                "gerer-les-odds",

                // Gestion les cibles
                "gerer-les-cibles",

                // Entités géographiques
                "voir-les-departements-geo", "voir-la-liste-des-communes", "voir-la-liste-des-arrondissements", "voir-la-liste-des-villages",

                // Secteurs d'intervention
                "voir-la-liste-des-grands-secteurs", "voir-la-liste-des-secteurs", "voir-la-liste-des-sous-secteurs",

                "voir-la-liste-des-types-intervention",

                // Financements
                "voir-la-liste-des-types-financement", "voir-la-liste-des-natures-financement", "voir-la-liste-des-sources-financement",

                // Programmes
                "voir-la-liste-des-programmes", "voir-la-liste-des-composants-programme",

                // Cadres stratégiques
                "voir-la-liste-des-axes-du-pag", "voir-la-liste-des-piliers-du-pag", "voir-la-liste-des-actions-du-pag", "voir-la-liste-des-orientations-strategique-du-pnd", "voir-la-liste-des-objectifs-strategique-du-pnd", "voir-la-liste-des-resultats-strategique-du-pnd",

                // Projets et idées
                "voir-la-liste-des-categories-de-projet",
                "voir-la-liste-des-idees-de-projet", "gerer-les-idees-projet", "creer-une-idee-de-projet", "modifier-une-idee-de-projet", "supprimer-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "obtenir-score-climatique", "obtenir-score-climatique-une-projet", "valider-le-score-climatique-une-idee-de-projet", "relancer-l-evaluation-climatique-une-idee-de-projet",

                "consulter-le-canevas-de-la-fiche-idee-de-projet", "remplir-le-canevas-de-la-fiche-idee-de-projet", "telecharger-la-fiche-synthese-une-idee-de-projet",

                "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "acceder-au-tableau-de-bord-climatique", "valider-le-score-d-analyse-climatique-interne-d-une-idee-de-projet", "valider-une-idee-de-projet-en-interne",

                "acceder-au-tableau-d-amc", "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",

                // Gestion les notes conceptuelle
                "gerer-les-notes-conceptuelle", "voir-la-liste-des-notes-conceptuelle", "creer-une-note-conceptuelle", "rediger-une-note-conceptuelle", "modifier-une-note-conceptuelle", "supprimer-une-note-conceptuelle", "commenter-une-note-conceptuelle", "voir-la-liste-des-commentaires-d-une-note-conceptuelle", "imprimer-une-note-conceptuelle", "televerser-une-note-conceptuelle", "attacher-des-documents-relatifs-a-une-note-conceptuelle", "voir-les-documents-relatifs-a-une-note-conceptuelle", "telecharger-les-documents-relatifs-a-une-note-conceptuelle",

                "consulter-la-fiche-de-redaction-d-une-note-conceptuelle", "imprimer-la-fiche-de-redaction-d-une-note-conceptuelle", "evaluer-une-note-conceptulle", "voir-le-resultats-d-evaluation-d-une-note-conceptuelle", "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle", "valider-l-etude-de-profil",

                "consulter-l-outil-d-analyse-d-une-note-conceptuelle", "imprimer-l-outil-d-analyse-d-une-note-conceptuelle",

                // TDRs
                "soumettre-un-tdr-de-prefaisabilite", "voir-la-liste-des-tdrs-de-prefaisabilite", "attacher-un-fichier-a-un-tdr-de-prefaisabilite", "supprimer-un-tdr-de-prefaisabilite", "apprecier-un-tdr-de-prefaisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-prefaisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-prefaisabilite",
                "soumettre-un-rapport-de-prefaisabilite","modifier-un-rapport-de-prefaisabilite", "supprimer-un-rapport-de-prefaisabilite", "telecharger-un-rapport-de-prefaisabilite", "valider-une-etude-de-prefaisabilite", "gerer-les-rapports-de-prefaisabilite", "voir-la-liste-des-rapports-de-prefaisabilite",

                "soumettre-un-tdr-de-faisabilite", "voir-la-liste-des-tdrs-de-faisabilite", "attacher-un-fichier-a-un-tdr-de-faisabilite", "supprimer-un-tdr-de-faisabilite", "apprecier-un-tdr-de-faisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-faisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-faisabilite",
                "soumettre-un-rapport-de-faisabilite","modifier-un-rapport-de-faisabilite", "supprimer-un-rapport-de-faisabilite", "telecharger-un-rapport-de-faisabilite", "valider-une-etude-de-faisabilite", "gerer-les-rapports-de-faisabilite", "voir-la-liste-des-rapports-de-faisabilite",

                "telecharger-un-rapport-d-evaluation-ex-ante", "valider-un-rapport-evaluation-ex-ante", "gerer-les-rapports-d-evaluation-ex-ante", "voir-la-liste-des-rapports-d-evaluation-ex-ante", "attacher-un-document-annexe-a-un-rapport-d-evaluation-ex-ante",

                "consulter-le-canevas-d-appreciation-d-un-tdr", "imprimer-le-canevas-d-appreciation-d-un-tdr",

                // Commentaires et fichiers
                "ajouter-commentaire", "voir-commentaires", "modifier-commentaire", "supprimer-commentaire",
                "telecharger-fichier", "upload-fichier", "supprimer-fichier",

                "voir-idees-projet", "voir-grille-evaluation-amc",
                "remplir-grille-amc", "enregistrer-fiche-synthese-amc", "modifier-fiche-synthese-amc", "emettre-commentaire", "rejeter-idee-idee", "recevoir-notification-validation-idee", "voir-historique-amc", "telecharger-fiche-synthese-amc",
                "voir-types-financement", "voir-sources-financement", "apprecier-tdr-faisabilite",
                "soumettre-rapport-faisabilite", "voir-rapports-etude"
            ],

            // DPAF
            "DPAF" => [
                // Gestion des utilisateurs
                "gerer-les-utilisateurs", "voir-la-liste-des-utilisateurs", "creer-un-utilisateur", "modifier-un-utilisateur", "supprimer-un-utilisateur",

                // Gestion des groupes-utilisateur
                "gerer-les-groupes-utilisateur", "voir-la-liste-des-groupes-utilisateur", "creer-un-groupe-utilisateur", "modifier-un-groupe-utilisateur", "supprimer-un-groupe-utilisateur", "assigner-un-role-a-un-groupe-utilisateur", "retirer-un-role-a-un-groupe-utilisateur", "ajouter-un-utilisateur-a-un-groupe-utilisateur", "ajouter-nouvel-utilisateur-a-un-groupe-utilisateur",

                // Gestion des rôles et permissions
                "gerer-les-roles", "voir-la-liste-des-roles", "creer-un-role", "modifier-un-role", "supprimer-un-role", "assigner-des-permissions-a-un-role", "retirer-des-permissions-a-un-role",

                // Gestion les odds
                "gerer-les-odds",

                // Gestion les cibles
                "gerer-les-cibles",

                // Entités géographiques
                "voir-les-departements-geo", "voir-la-liste-des-communes", "voir-la-liste-des-arrondissements", "voir-la-liste-des-villages",

                // Secteurs d'intervention
                "voir-la-liste-des-grands-secteurs", "voir-la-liste-des-secteurs", "voir-la-liste-des-sous-secteurs",

                "voir-la-liste-des-types-intervention",

                // Financements
                "voir-la-liste-des-types-financement", "voir-la-liste-des-natures-financement", "voir-la-liste-des-sources-financement",

                // Programmes
                "voir-la-liste-des-programmes", "voir-la-liste-des-composants-programme",

                // Cadres stratégiques
                "voir-la-liste-des-axes-du-pag", "voir-la-liste-des-piliers-du-pag", "voir-la-liste-des-actions-du-pag", "voir-la-liste-des-orientations-strategique-du-pnd", "voir-la-liste-des-objectifs-strategique-du-pnd", "voir-la-liste-des-resultats-strategique-du-pnd",

                // Projets et idées
                "voir-la-liste-des-categories-de-projet",
                "voir-la-liste-des-idees-de-projet", "gerer-les-idees-projet", "creer-une-idee-de-projet", "modifier-une-idee-de-projet", "supprimer-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "obtenir-score-climatique", "obtenir-score-climatique-une-projet", "valider-le-score-climatique-une-idee-de-projet", "relancer-l-evaluation-climatique-une-idee-de-projet",

                "consulter-le-canevas-de-la-fiche-idee-de-projet", "remplir-le-canevas-de-la-fiche-idee-de-projet", "telecharger-la-fiche-synthese-une-idee-de-projet",

                "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "acceder-au-tableau-de-bord-climatique", "valider-le-score-d-analyse-climatique-interne-d-une-idee-de-projet", "valider-une-idee-de-projet-en-interne",

                "acceder-au-tableau-d-amc", "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",

                // Gestion les notes conceptuelle
                "gerer-les-notes-conceptuelle", "voir-la-liste-des-notes-conceptuelle", "creer-une-note-conceptuelle", "rediger-une-note-conceptuelle", "modifier-une-note-conceptuelle", "supprimer-une-note-conceptuelle", "commenter-une-note-conceptuelle", "voir-la-liste-des-commentaires-d-une-note-conceptuelle", "imprimer-une-note-conceptuelle", "televerser-une-note-conceptuelle", "attacher-des-documents-relatifs-a-une-note-conceptuelle", "voir-les-documents-relatifs-a-une-note-conceptuelle", "telecharger-les-documents-relatifs-a-une-note-conceptuelle",

                "consulter-la-fiche-de-redaction-d-une-note-conceptuelle", "imprimer-la-fiche-de-redaction-d-une-note-conceptuelle", "evaluer-une-note-conceptulle", "voir-le-resultats-d-evaluation-d-une-note-conceptuelle", "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle", "valider-l-etude-de-profil",

                "consulter-l-outil-d-analyse-d-une-note-conceptuelle", "imprimer-l-outil-d-analyse-d-une-note-conceptuelle",

                // TDRs
                "soumettre-un-tdr-de-prefaisabilite", "voir-la-liste-des-tdrs-de-prefaisabilite", "attacher-un-fichier-a-un-tdr-de-prefaisabilite", "supprimer-un-tdr-de-prefaisabilite", "apprecier-un-tdr-de-prefaisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-prefaisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-prefaisabilite",
                "soumettre-un-rapport-de-prefaisabilite","modifier-un-rapport-de-prefaisabilite", "supprimer-un-rapport-de-prefaisabilite", "telecharger-un-rapport-de-prefaisabilite", "valider-une-etude-de-prefaisabilite", "gerer-les-rapports-de-prefaisabilite", "voir-la-liste-des-rapports-de-prefaisabilite",

                "soumettre-un-tdr-de-faisabilite", "voir-la-liste-des-tdrs-de-faisabilite", "attacher-un-fichier-a-un-tdr-de-faisabilite", "supprimer-un-tdr-de-faisabilite", "apprecier-un-tdr-de-faisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-faisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-faisabilite",
                "soumettre-un-rapport-de-faisabilite","modifier-un-rapport-de-faisabilite", "supprimer-un-rapport-de-faisabilite", "telecharger-un-rapport-de-faisabilite", "valider-une-etude-de-faisabilite", "gerer-les-rapports-de-faisabilite", "voir-la-liste-des-rapports-de-faisabilite",

                "soumettre-un-rapport-d-evaluation-ex-ante","modifier-un-rapport-d-evaluation-ex-ante", "supprimer-un-rapport-d-evaluation-ex-ante", "telecharger-un-rapport-d-evaluation-ex-ante", "gerer-les-rapports-d-evaluation-ex-ante", "voir-la-liste-des-rapports-d-evaluation-ex-ante", "attacher-un-document-annexe-a-un-rapport-d-evaluation-ex-ante",

                "consulter-le-canevas-d-appreciation-d-un-tdr", "imprimer-le-canevas-d-appreciation-d-un-tdr",

                // Commentaires et fichiers
                "ajouter-commentaire", "voir-commentaires", "modifier-commentaire", "supprimer-commentaire",
                "telecharger-fichier", "upload-fichier", "supprimer-fichier",
            ],

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
                        'slug' => strtolower(str_replace(' ', '-', $role_name))
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

        $adminPerson = Personne::firstOrCreate([
            "nom" => "Admin",
            "prenom" => "Admin",
            "poste" => "Administrateur general"

        ]);

        // Supprimer les anciens utilisateurs et créer les nouveaux
        DB::table('users')->truncate();

        User::create([
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'password' => Hash::make("SuperAdmin123!"),
            'personneId' => $adminPerson->id,
            'roleId' => Role::where("slug", "super-admin")->first()->id,
            'last_connection' => now()->subHours(2),
            "provider" => "local",
            "provider_user_id" => "jsognon8@gmail.com",
            "username" => "jsognon8@gmail.com",
            'email' => "jsognon8@gmail.com",
            "status" => "actif",
            "last_connection" =>  now()->subHours(2),
            'ip_address' => '127.0.0.1',
            "created_at" => now(),
            "settings" => null,
            "person" => null,
            "keycloak_id" => null,
            "type" => "super-admin",
            "lastRequest" =>  now()->subHours(2),
            "profilable_id" => null,
            "profilable_type" => null,
            "account_verification_request_sent_at" => null,
            "password_update_at" => null,
            "last_password_remember" => null,
            "token" => null,
            "link_is_valide" => false,
        ]);
    }
}
