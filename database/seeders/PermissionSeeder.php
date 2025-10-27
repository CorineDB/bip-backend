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
        /*DB::table('roles')->truncate();
        DB::table('permissions')->truncate();*/

        $espaces = ["administration-general", "dpaf", "dgpb", "organisation"];

        $roles_par_espace = [
            "administration-general" => ["Super Admin"],
            "dpaf" => ["DPAF"],
            "dgpb" => ["DGPD", "Analyste DGPD"],
            "organisation" => ["Organisation", "Responsable projet"]
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

            // Gestion des organisations
            "gerer-les-organisations", "voir-la-liste-des-organisations", "creer-une-organisation", "modifier-une-organisation", "supprimer-une-organisation",

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

            // Projets et idées - Catégories
            "voir-la-liste-des-categories-de-projet", "gerer-les-categories-de-projet", "creer-une-categorie-de-projet", "modifier-une-categorie-de-projet", "supprimer-une-categorie-de-projet",

            // Gestion des idées de projet
            "voir-la-liste-des-idees-de-projet", "gerer-les-idees-projet", "creer-une-idee-de-projet", "modifier-une-idee-de-projet", "supprimer-une-idee-de-projet", "consulter-une-idee-de-projet", "exporter-une-idee-de-projet", "imprimer-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "obtenir-score-climatique", "obtenir-score-climatique-une-projet", "valider-le-score-climatique-une-idee-de-projet", "relancer-l-evaluation-climatique-une-idee-de-projet", "commenter-une-idee-de-projet", "voir-les-commentaires-d-une-idee-de-projet", "attacher-des-documents-a-une-idee-de-projet", "voir-les-documents-d-une-idee-de-projet", "telecharger-les-documents-d-une-idee-de-projet",

            // Gestion des projets
            "voir-la-liste-des-projets", "consulter-un-projet", "exporter-un-projet", "imprimer-un-projet", "commenter-un-projet", "voir-les-commentaires-d-un-projet", "attacher-des-documents-a-un-projet", "voir-les-documents-d-un-projet", "telecharger-les-documents-d-un-projet", "suivre-avancement-projet", "mettre-a-jour-statut-projet", "generer-rapport-projet", "voir-historique-projet",

            "creer-le-canevas-de-la-fiche-idee-de-projet", "modifier-le-canevas-de-la-fiche-idee-de-projet", "consulter-le-canevas-de-la-fiche-idee-de-projet", "remplir-le-canevas-de-la-fiche-idee-de-projet", "telecharger-la-fiche-synthese-une-idee-de-projet",

            "creer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet", "modifier-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet", "consulter-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet", "effectuer-evaluation-pertinence-idee-projet", "valider-le-score-de-pertinence-d-une-idee-de-projet", "relancer-l-evaluation-de-pertinence-d-une-idee-de-projet", "acceder-au-tableau-de-bord-de-pertinence", "exporter-le-resultats-de-l-analyse-d-une-idee-de-projet", "commenter-le-resultats-de-l-analyse-d-une-idee-de-projet",

            "creer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "modifier-la-grille-d-analyse-climatique-d-une-idee-de-projet", "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "acceder-au-tableau-de-bord-climatique", "valider-le-score-d-analyse-climatique-interne-d-une-idee-de-projet", "valider-une-idee-de-projet-en-interne", "exporter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet", "commenter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",

            "creer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet", "modifier-la-grille-d-analyse-multi-critere-d-une-idee-de-projet", "consulter-la-grille-d-analyse-multi-critere-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet", "effectuer-l-analyse-climatique-d-une-idee-de-projet", "acceder-au-tableau-d-amc", "effectuer-l-amc-d-une-idee-de-projet", "valider-une-idee-de-projet-a-projet", "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet", "commenter-le-resultats-de-l-amc-d-une-idee-de-projet",

            // Gestion les notes conceptuelle
            "gerer-les-notes-conceptuelle", "voir-la-liste-des-notes-conceptuelle", "creer-une-note-conceptuelle", "rediger-une-note-conceptuelle", "modifier-une-note-conceptuelle", "supprimer-une-note-conceptuelle", "commenter-une-note-conceptuelle", "voir-la-liste-des-commentaires-d-une-note-conceptuelle", "imprimer-une-note-conceptuelle", "televerser-une-note-conceptuelle", "attacher-des-documents-relatifs-a-une-note-conceptuelle", "voir-les-documents-relatifs-a-une-note-conceptuelle", "telecharger-les-documents-relatifs-a-une-note-conceptuelle",

            "creer-la-fiche-de-redaction-d-une-note-conceptuelle", "modifier-la-fiche-de-redaction-d-une-note-conceptuelle", "consulter-la-fiche-de-redaction-d-une-note-conceptuelle", "imprimer-la-fiche-de-redaction-d-une-note-conceptuelle", "evaluer-une-note-conceptulle", "voir-le-resultats-d-evaluation-d-une-note-conceptuelle", "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle", "valider-l-etude-de-profil", "commenter-l-appreciation-d-une-note-conceptuelle", "consulter-les-details-de-la-validation-de-l-etude-de-profil", "commenter-la-decision-de-validation-de-l-etude-de-profil",

            "creer-l-outil-d-analyse-d-une-note-conceptuelle", "modifier-l-outil-d-analyse-d-une-note-conceptuelle", "consulter-l-outil-d-analyse-d-une-note-conceptuelle", "imprimer-l-outil-d-analyse-d-une-note-conceptuelle",

            // FAISABILITE PRELIMINAIRE
            "soumettre-un-rapport-de-faisabilite-preliminaire","modifier-un-rapport-de-faisabilite-preliminaire", "supprimer-un-rapport-de-faisabilite-preliminaire", "telecharger-un-rapport-de-faisabilite-preliminaire", "commenter-un-rapport-de-faisabilite-preliminaire", "gerer-les-rapports-de-faisabilite-preliminaire", "voir-la-liste-des-rapports-de-faisabilite-preliminaire",


            // TDRs
            "soumettre-un-tdr-de-prefaisabilite", "voir-la-liste-des-tdrs-de-prefaisabilite", "attacher-un-fichier-a-un-tdr-de-prefaisabilite", "supprimer-un-tdr-de-prefaisabilite", "apprecier-un-tdr-de-prefaisabilite", "consulter-le-details-d-appreciation-d-un-tdr-de-prefaisabilite", "valider-un-tdr-de-prefaisabilite", "voir-details-de-l-appreciation-un-tdr-de-prefaisabilite", "exporter-l-appreciation-d-un-tdr-de-prefaisabilite", "commenter-l-appreciation-d-un-tdr-de-prefaisabilite",
            "soumettre-un-rapport-de-prefaisabilite","modifier-un-rapport-de-prefaisabilite", "supprimer-un-rapport-de-prefaisabilite", "telecharger-un-rapport-de-prefaisabilite", "valider-une-etude-de-prefaisabilite", "consulter-les-details-de-la-validation-de-l-etude-de-prefaisabilite", "commenter-la-decision-de-validation-de-l-etude-de-prefaisabilite", "gerer-les-rapports-de-prefaisabilite", "voir-la-liste-des-rapports-de-prefaisabilite",

            "soumettre-un-tdr-de-faisabilite", "voir-la-liste-des-tdrs-de-faisabilite", "attacher-un-fichier-a-un-tdr-de-faisabilite", "supprimer-un-tdr-de-faisabilite", "apprecier-un-tdr-de-faisabilite", "valider-un-tdr-de-faisabilite", "voir-details-de-l-appreciation-un-tdr-de-faisabilite", "exporter-l-appreciation-d-un-tdr-de-faisabilite", "commenter-l-appreciation-d-un-tdr-de-faisabilite",
            "soumettre-un-rapport-de-faisabilite","modifier-un-rapport-de-faisabilite", "supprimer-un-rapport-de-faisabilite", "telecharger-un-rapport-de-faisabilite", "valider-une-etude-de-faisabilite", "consulter-les-details-de-la-validation-de-l-etude-de-faisabilite", "commenter-la-decision-de-validation-de-l-etude-de-faisabilite", "gerer-les-rapports-de-faisabilite", "voir-la-liste-des-rapports-de-faisabilite",

            // Évaluation Ex-Ante
            "soumettre-un-rapport-d-evaluation-ex-ante", "modifier-un-rapport-d-evaluation-ex-ante", "supprimer-un-rapport-d-evaluation-ex-ante", "consulter-un-rapport-d-evaluation-ex-ante", "telecharger-un-rapport-d-evaluation-ex-ante", "imprimer-un-rapport-d-evaluation-ex-ante", "exporter-un-rapport-d-evaluation-ex-ante", "valider-un-rapport-evaluation-ex-ante", "consulter-les-details-de-la-validation-de-la-validation-finale", "commenter-la-decision-de-validation-finale", "rejeter-un-rapport-evaluation-ex-ante", "gerer-les-rapports-d-evaluation-ex-ante", "voir-la-liste-des-rapports-d-evaluation-ex-ante", "attacher-un-document-annexe-a-un-rapport-d-evaluation-ex-ante", "voir-les-documents-annexes-d-un-rapport-d-evaluation-ex-ante", "telecharger-les-documents-annexes-d-un-rapport-d-evaluation-ex-ante", "supprimer-un-document-annexe-d-un-rapport-d-evaluation-ex-ante", "commenter-un-rapport-d-evaluation-ex-ante", "voir-les-commentaires-d-un-rapport-d-evaluation-ex-ante", "voir-historique-rapport-d-evaluation-ex-ante",

            "creer-le-canevas-d-appreciation-d-un-tdr", "modifier-le-canevas-d-appreciation-d-un-tdr", "consulter-le-canevas-d-appreciation-d-un-tdr", "imprimer-le-canevas-d-appreciation-d-un-tdr",

            // Documents et TDR
            "voir-la-liste-des-canevas", "gerer-les-canevas", "creer-un-canevas", "modifier-un-canevas", "supprimer-un-canevas", "imprimer-un-canevas",

            "voir-la-liste-des-canevas", "telecharger-un-canevas-analyse-idee", "modifier-canevas-analyse-idee", "voir-le-canevas-de-la-fiche-idee", "modifier-le-canevas-de-la-fiche-idee", "telecharger-canevas-fiche-idee", "modifier-canevas-grille-evaluation-climatique", "modifier-canevas-grille-evaluation-amc", "modifier-canevas-note-idee", "gerer-documents", "telecharger-documents",
            "creer-un-tdr", "modifier-un-tdr",
            "voir-tdr-prefaisabilite", "voir-tdr-faisabilite", "telecharger-tdr-prefaisabilite",
            "telecharger-tdr-faisabilite", "soumettre-tdr-faisabilite", "soumettre-tdr-prefaisabilite",

            // Évaluations et validations

            "televerser-un-fichier", "partager-un-fichier", "supprimer-un-fichier", "telecharger-un-fichier", 'consulter-un-fichier',

            // Commentaires et fichiers
            "ajouter-commentaire", "voir-commentaires", "modifier-commentaire", "supprimer-commentaire",
            "telecharger-fichier", "upload-fichier", "supprimer-fichier",

            // === CANEVAS DE RÉDACTION - Note Conceptuelle (instance unique) ===
            "creer-le-canevas-de-redaction-note-conceptuelle",
            "modifier-le-canevas-de-redaction-note-conceptuelle",
            "consulter-le-canevas-de-redaction-note-conceptuelle",
            "remplir-le-canevas-de-redaction-note-conceptuelle",
            "imprimer-le-canevas-de-redaction-note-conceptuelle",
            "exporter-le-canevas-de-redaction-note-conceptuelle",
            "telecharger-le-canevas-de-redaction-note-conceptuelle",
            "restaurer-version-anterieure-canevas-note-conceptuelle",
            "voir-historique-canevas-note-conceptuelle",

            // === CHECKLIST D'APPRÉCIATION - TDR Préfaisabilité (instance unique) ===
            "creer-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
            "modifier-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
            "consulter-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
            "remplir-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
            "imprimer-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
            "exporter-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
            "telecharger-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
            "restaurer-version-anterieure-checklist-appreciation-tdr-prefaisabilite",

            // === CHECKLIST D'APPRÉCIATION - TDR Faisabilité (instance unique) ===
            "creer-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
            "modifier-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
            "consulter-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
            "remplir-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
            "imprimer-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
            "exporter-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
            "telecharger-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
            "restaurer-version-anterieure-checklist-appreciation-tdr-faisabilite",

            // === CHECKLIST DE SUIVI - Rapport Préfaisabilité (instance unique) ===
            "creer-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
            "modifier-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
            "consulter-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
            "remplir-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
            "imprimer-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
            "exporter-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
            "telecharger-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
            "restaurer-version-anterieure-checklist-suivi-rapport-prefaisabilite",

            // === CHECKLIST DE SUIVI - Étude Faisabilité Technique ===
            "creer-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
            "modifier-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
            "consulter-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
            "remplir-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
            "imprimer-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
            "exporter-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
            "telecharger-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",

            // === CHECKLIST DE SUIVI - Étude Faisabilité Économique ===
            "creer-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
            "modifier-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
            "consulter-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
            "remplir-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
            "imprimer-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
            "exporter-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
            "telecharger-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",

            // === CHECKLIST DE SUIVI - Étude Faisabilité Marché ===
            "creer-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
            "modifier-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
            "consulter-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
            "remplir-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
            "imprimer-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
            "exporter-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
            "telecharger-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",

            // === CHECKLIST DE SUIVI - Étude Faisabilité Organisationnelle et Juridique ===
            "creer-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
            "modifier-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
            "consulter-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
            "remplir-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
            "imprimer-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
            "exporter-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
            "telecharger-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",

            // === CHECKLIST DE SUIVI - Étude Impact Environnemental et Social ===
            "creer-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
            "modifier-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
            "consulter-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
            "remplir-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
            "imprimer-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
            "exporter-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
            "telecharger-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",

            // === CHECKLIST DE SUIVI - Analyse Faisabilité Financière ===
            "creer-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
            "modifier-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
            "consulter-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
            "remplir-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
            "imprimer-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
            "exporter-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
            "telecharger-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",

            // === CHECKLIST - Assurance Qualité Rapport Faisabilité ===
            "creer-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
            "modifier-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
            "consulter-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
            "remplir-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
            "imprimer-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
            "exporter-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
            "telecharger-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",

            // === CHECKLIST - Contrôle Qualité Rapport Faisabilité Préliminaire ===
            "creer-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
            "modifier-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
            "consulter-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
            "remplir-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
            "imprimer-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
            "exporter-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
            "telecharger-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",

            // === GRILLE D'ÉVALUATION - Pertinence (instance unique) ===
            "creer-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
            "modifier-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
            "consulter-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
            "imprimer-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
            "exporter-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
            "telecharger-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
            "restaurer-version-anterieure-de-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
            "voir-historique-grille-evaluation-de-la-pertinence-des-idees-de-projet",

            // === GRILLE D'ÉVALUATION - Climatique (instance unique) ===
            "creer-l-outil-d-evaluation-climatique-des-idees-de-projet",
            "modifier-l-outil-d-evaluation-climatique-des-idees-de-projet",
            "consulter-l-outil-d-evaluation-climatique-des-idees-de-projet",
            "imprimer-l-outil-d-evaluation-climatique-des-idees-de-projet",
            "exporter-l-outil-d-evaluation-climatique-des-idees-de-projet",
            "telecharger-l-outil-d-evaluation-climatique-des-idees-de-projet",
            "voir-historique-de-l-outil-d-evaluation-climatique-des-idees-de-projet",

            // === GRILLE D'ÉVALUATION - Analyse Multi-Critères (AMC) (instance unique) ===
            "creer-l-outil-d-analyse-multicritere-des-idees-de-projet",
            "modifier-l-outil-d-analyse-multicritere-des-idees-de-projet",
            "consulter-l-outil-d-analyse-multicritere-des-idees-de-projet",
            "imprimer-l-outil-d-analyse-multicritere-des-idees-de-projet",
            "exporter-l-outil-d-analyse-multicritere-des-idees-de-projet",
            "telecharger--l-outil-d-analyse-multicritere-des-idees-de-projet",
            "voir-historique-outil-d-analyse-multicritere-des-idees-de-projet",

            // === OUTIL D'ANALYSE - Note Conceptuelle (instance unique) ===
            "creer-le-check-liste-d-appreciation-des-notes-conceptuelle",
            "modifier-le-check-liste-d-appreciation-des-notes-conceptuelle",
            "consulter-le-check-liste-d-appreciation-des-notes-conceptuelle",
            "remplir-le-check-liste-d-appreciation-des-notes-conceptuelle",
            "imprimer-le-check-liste-d-appreciation-des-notes-conceptuelle",
            "exporter-le-check-liste-d-appreciation-des-notes-conceptuelle",
            "telecharger-le-check-liste-d-appreciation-des-notes-conceptuelle",
            "restaurer-version-anterieure-checklist-des-notes-conceptuelle",
        ];

        $permissions_par_role = [
            // Administration Générale
            "Super Admin" => [],

            // DPAF
            "Responsable Projet" => [],
            "Organisation" => [],
            "Analyste DGPD" => [],

            "DGPD" => [],

            // DPAF
            "DPAF" => [],

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
        //DB::table('users')->truncate();

        User::updateOrCreate([
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
