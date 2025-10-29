<?php

namespace Database\Seeders;

use App\Models\Dpaf;
use App\Models\GroupeUtilisateur;
use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Personne;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganisationsSeeder extends Seeder
{
    // Liste des slugs de permissions à attacher
    protected $permissionSlugsRP = [
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
    ];

    // DPAF
    protected $dpafPermissionsSlugs = [
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
    ];

    public function run(): void
    {
        DB::table('organisations')->truncate();
        /* $organisations = [
            // Ministères
            [
                'nom' => 'Ministère du Plan',
                'slug' => 'ministere-plan',
                'description' => 'Ministère en charge de la planification nationale',
                'type' => 'ministere',
                'parentId' => null
            ],
            [
                'nom' => 'Ministère des Finances',
                'slug' => 'ministere-finances',
                'description' => 'Ministère des finances publiques',
                'type' => 'ministere',
                'parentId' => null
            ]
        ]; */
        $ministeres = [
            [
                'nom' => 'Ministère du Plan et du Développement',
                'slug' => 'ministere-plan',
                'description' => 'Ministère en charge de la planification nationale',
                'type' => 'ministere',
                'parentId' => null,
                'enfants' => [
                    [
                        'nom' => 'Agence Nationale de la Statistique et de la Démographie (ANSD)',
                        'slug' => 'ansd',
                        'description' => 'Agence en charge des statistiques nationales',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Direction de la Planification et de la Prospective',
                        'slug' => 'direction-planification',
                        'description' => 'Direction responsable des plans stratégiques',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Partenaire Technique: Banque Mondiale',
                        'slug' => 'partenaire-banque-mondiale',
                        'description' => 'Partenaire au développement',
                        'type' => 'partenaire',
                    ],
                ]
            ],
            [
                'nom' => 'Ministère de la Santé',
                'slug' => 'ministere-sante',
                'description' => 'Ministère en charge des politiques de santé publique',
                'type' => 'ministere',
                'parentId' => null,
                'enfants' => [
                    [
                        'nom' => 'Centre National Hospitalier Universitaire Hubert Maga',
                        'slug' => 'cnhu-hubert-maga',
                        'description' => 'Principal hôpital universitaire du pays',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Agence Nationale de Lutte contre le Sida',
                        'slug' => 'anls',
                        'description' => 'Agence nationale pour la prévention du VIH/SIDA',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Partenaire Technique: OMS',
                        'slug' => 'partenaire-oms',
                        'description' => 'Organisation mondiale de la santé',
                        'type' => 'partenaire',
                    ],
                ]
            ],
            [
                'nom' => 'Ministère de l’Environnement et du Développement Durable',
                'slug' => 'ministere-environnement',
                'description' => 'Ministère chargé des questions environnementales',
                'type' => 'ministere',
                'parentId' => null,
                'enfants' => [
                    [
                        'nom' => 'Agence Nationale de Protection de l’Environnement (ANPE)',
                        'slug' => 'anpe',
                        'description' => 'Agence en charge de la protection de l’environnement',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Direction des Ressources Naturelles',
                        'slug' => 'direction-ressources-naturelles',
                        'description' => 'Direction en charge de la gestion durable des ressources',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Partenaire Technique: PNUD',
                        'slug' => 'partenaire-pnud',
                        'description' => 'Programme des Nations Unies pour le Développement',
                        'type' => 'partenaire',
                    ],
                ]
            ],
            [
                'nom' => 'Ministère de l’Agriculture, de l’Élevage et de la Pêche',
                'slug' => 'ministere-agriculture',
                'description' => 'Ministère chargé des politiques agricoles et rurales',
                'type' => 'ministere',
                'parentId' => null,
                'enfants' => [
                    [
                        'nom' => 'Institut National des Recherches Agricoles du Bénin (INRAB)',
                        'slug' => 'inrab',
                        'description' => 'Institut de recherche agricole',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Agence Nationale de Sécurité Sanitaire des Aliments (ANSSA)',
                        'slug' => 'anssa',
                        'description' => 'Agence en charge de la sécurité alimentaire',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Partenaire Technique: FAO',
                        'slug' => 'partenaire-fao',
                        'description' => 'Organisation des Nations Unies pour l’alimentation et l’agriculture',
                        'type' => 'partenaire',
                    ],
                ]
            ],
            [
                'nom' => 'Ministère de l’Économie et des Finances',
                'slug' => 'ministere-economie-finances',
                'description' => 'Ministère en charge des finances publiques, du budget et du trésor',
                'type' => 'ministere',
                'parentId' => null,
                'enfants' => [
                    [
                        'nom' => 'Direction Générale du Trésor et de la Comptabilité Publique',
                        'slug' => 'direction-tresor-comptabilite',
                        'description' => 'Gestion des finances publiques et du trésor',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Agence de Gestion de la Dette Publique',
                        'slug' => 'agence-gestion-dette',
                        'description' => 'Gestion et suivi de la dette publique',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Partenaire Technique: FMI',
                        'slug' => 'partenaire-fmi',
                        'description' => 'Fonds Monétaire International',
                        'type' => 'partenaire',
                    ],
                    [
                        'nom' => 'Partenaire Technique: Banque Africaine de Développement (BAD)',
                        'slug' => 'partenaire-bad',
                        'description' => 'Banque de financement du développement en Afrique',
                        'type' => 'partenaire',
                    ],
                ]
            ],
            [
                'nom' => 'Ministère du Numérique et de la Digitalisation',
                'slug' => 'ministere-numerique',
                'description' => 'Ministère en charge des politiques numériques, TIC et digitalisation',
                'type' => 'ministere',
                'parentId' => null,
                'enfants' => [
                    [
                        'nom' => 'Agence Nationale de la Sécurité Informatique (ANSI)',
                        'slug' => 'ansi',
                        'description' => 'Agence responsable de la cybersécurité nationale',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Agence de Régulation des Technologies de l’Information et de la Communication (ARTIC)',
                        'slug' => 'artic',
                        'description' => 'Agence de régulation du secteur des télécommunications',
                        'type' => 'etatique',
                    ],
                    [
                        'nom' => 'Direction de la Transformation Digitale',
                        'slug' => 'direction-transformation-digitale',
                        'description' => 'Direction en charge de la digitalisation des services publics',
                        'type' => 'etatique',
                    ]
                ]
            ]
            // Ajoute autant que nécessaire...
        ];

        /* foreach ($organisations as $organisation) {
            DB::table('organisations')->updateOrInsert(
                ['slug' => $organisation['slug']],
                [
                    'nom' => $organisation['nom'],
                    'slug' => $organisation['slug'],
                    'description' => $organisation['description'],
                    'type' => $organisation['type'],
                    'parentId' => $organisation['parentId'],
                ]
            );
        } */

        // Récupérer le rôle Organisation
        $roleOrganisation = Role::updateOrCreate(['slug' => 'organisation'], ['nom' => 'Organisation']);

        foreach ($ministeres as $ministereData) {

            // Extraire les enfants avant l'insertion (ce n'est pas un champ de la table)
            //$enfants = $ministereData['enfants'] ?? [];
            unset($ministereData['enfants']);

            $ministere = Organisation::updateOrCreate(
                ['nom' => $ministereData['nom']/* , 'slug' => $ministereData['slug'] */],
                $ministereData
            );

            // Créer un email admin unique par ministère, ex: admin.MPD@bj
            $initiales = $this->genererInitiales($ministere->nom);
            $adminEmail = 'admin.' . strtolower($initiales) . '@bj';

            $adminMinistere = User::where('email', $adminEmail)->first();

            if (!$adminMinistere) {
                $adminMinisterePersonne = Personne::updateOrCreate(
                    [
                        'nom' => 'Admin',
                        'prenom' => ucfirst($ministere->slug),
                        'organismeId' => $ministere->id
                    ],
                    [
                        'poste' => 'Administrateur Ministère',
                        'organismeId' => $ministere->id
                    ]
                );

                $passwordMinistere = 'Ministere123!';

                $adminMinistere = User::updateOrCreate(['email' => $adminEmail], [
                    'provider' => 'local',
                    'provider_user_id' => $adminEmail,
                    'username' => $adminEmail,
                    'email' => $adminEmail,
                    'status' => 'actif',
                    'is_email_verified' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make($passwordMinistere),
                    'personneId' => $adminMinisterePersonne->id,
                    'roleId' => $roleOrganisation->id,
                    'last_connection' => now(),
                    'ip_address' => '127.0.0.1',
                    'type' => 'organisation',
                    'profilable_id' => $ministere->id,
                    'profilable_type' => get_class($ministere),
                    'account_verification_request_sent_at' => Carbon::now(),
                    'token' => str_replace(['/', '\\', '.'], '', Hash::make($ministere->id . Hash::make($adminEmail) . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                    'link_is_valide' => true,
                    'created_at' => now(),
                    'lastRequest' => now()
                ]);

                $adminMinistere->roles()->sync([$roleOrganisation->id]);

                $this->command->info("✅ Admin créé pour le ministère {$ministere->nom}");
                $this->command->info("📧 Email : {$adminEmail}");
                $this->command->info("🔑 Mot de passe : {$passwordMinistere}");
            } else {
                $this->command->info("ℹ️ Le compte admin existe déjà pour le ministère {$ministere->nom}");
            }

            /*$roleDpaf = Role::firstOrCreate([
                'slug' => 'dpaf',
                'roleable_type' => get_class($ministere),
                'roleable_id' => $ministere->id
            ], [
                'nom' => 'DPAF',
                'roleable_type' => get_class($ministere),
                'roleable_id' => $ministere->id
            ]);

            if (!$roleDpaf) {
                $this->command->error('⚠️ Le rôle DPAF n\'existe pas.');
                return;
            }

            // Récupérer les IDs des permissions correspondantes aux slugs
            $permissionIds = Permission::whereIn('slug', $this->dpafPermissionsSlugs)->pluck('id')->toArray();

            // Synchroniser les permissions au rôle
            $roleDpaf->permissions()->sync($permissionIds);

            $this->command->info("✅ Rôle Responsable hiérachique créé pour {$ministere->nom}");

            // --- DPAF ---
            $dpaf = Dpaf::firstOrCreate(
                [
                    'slug' => 'dpaf',
                    'id_ministere' => $ministere->id
                ],
                [
                    'nom' => 'Direction de la Programmation et de l\'Analyse Financière',
                    'description' => 'Direction de la Programmation et de l\'Analyse Financière du ' . $ministere->nom
                ]
            );

            // --- Admin DPAF ---
            $adminDpafEmail = 'admin.dpaf.' . $ministere->slug . '@bj';
            $adminDpaf = User::where('email', $adminDpafEmail)->first();

            if (!$adminDpaf) {
                $adminDpafPersonne = Personne::firstOrCreate(
                    [
                        'nom' => 'Admin',
                        'prenom' => 'DPAF',
                        'organismeId' => $ministere->id
                    ],
                    [
                        'poste' => 'Administrateur DPAF',
                        'organismeId' => $ministere->id
                    ]
                );

                $passwordDpaf = 'DPAF123!';

                $adminDpaf = User::create([
                    'provider' => 'local',
                    'provider_user_id' => $adminDpafEmail,
                    'username' => $adminDpafEmail,
                    'email' => $adminDpafEmail,
                    'status' => 'actif',
                    'is_email_verified' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make($passwordDpaf),
                    'personneId' => $adminDpafPersonne->id,
                    'roleId' => $roleDpaf->id,
                    'last_connection' => now(),
                    'ip_address' => '127.0.0.1',
                    'type' => 'dpaf',
                    'profilable_id' => $dpaf->id,
                    'profilable_type' => get_class($dpaf),
                    'account_verification_request_sent_at' => Carbon::now(),
                    'token' => str_replace(['/', '\\', '.'], '', Hash::make($dpaf->id . Hash::make($adminDpafEmail) . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                    'link_is_valide' => true,
                    'created_at' => now(),
                    'lastRequest' => now()
                ]);

                $adminDpaf->roles()->attach([$roleDpaf->id]);

                $this->command->info("✅ Admin DPAF créé pour le ministère {$ministere->nom}");
                $this->command->info("📧 Email : {$adminDpafEmail}");
                $this->command->info("🔑 Mot de passe : {$passwordDpaf}");
            } else {
                $this->command->info("ℹ️ Le compte admin DPAF existe déjà pour le ministère {$ministere->nom}");
            }

            $slugMin = $ministere->slug;

            /*
             * Rôle Responsable projet du ministère
             /
            $roleResponsableProjet = Role::firstOrCreate(
                [
                    'slug' => 'responsable-projet',
                    'roleable_type' => get_class($ministere),
                    'roleable_id' => $ministere->id,
                ],
                [
                    'nom' => 'Responsable projet',
                    'description' => 'Responsable de projet du ' . $ministere->nom,
                    'roleable_type' => get_class($ministere),
                    'roleable_id' => $ministere->id,
                ]
            );

            // Récupérer les IDs des permissions correspondantes aux slugs
            $permissionIds = Permission::whereIn('slug', $this->permissionSlugsRP)->pluck('id')->toArray();

            // Synchroniser les permissions au rôle
            $roleResponsableProjet->permissions()->sync($permissionIds);

            $this->command->info("✅ Rôle Responsable projet créé pour {$ministere->nom}");

            /*
             * Utilisateur Responsable projet
             /
            $emailResponsableProjet = "responsable-projet.{$slugMin}@ministere.bj";
            $responsableProjet = User::where('email', $emailResponsableProjet)->first();

            if (!$responsableProjet) {
                $responsableProjetPersonne = Personne::firstOrCreate(
                    [
                        'nom' => 'Responsable',
                        'prenom' => 'Projet',
                        'organismeId' => $ministere->id
                    ],
                    [
                        'poste' => 'Responsable de projet',
                        'organismeId' => $ministere->id
                    ]
                );

                $passwordResponsable = 'ResponsableProjet123!';

                $responsableProjet = User::create([
                    'provider' => 'local',
                    'provider_user_id' => $emailResponsableProjet,
                    'username' => $emailResponsableProjet,
                    'email' => $emailResponsableProjet,
                    'status' => 'actif',
                    'is_email_verified' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make($passwordResponsable),
                    'personneId' => $responsableProjetPersonne->id,
                    'roleId' => $roleResponsableProjet->id,
                    'last_connection' => now(),
                    'ip_address' => '127.0.0.1',
                    'type' => 'responsable-projet',
                    'profilable_id' => $ministere->id,
                    'profilable_type' => get_class($ministere),
                    'account_verification_request_sent_at' => Carbon::now(),
                    'token' => str_replace(['/', '\\', '.'], '', Hash::make($ministere->id . Hash::make($emailResponsableProjet) . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                    'link_is_valide' => true,
                    'created_at' => now(),
                    'lastRequest' => now()
                ]);

                $responsableProjet->roles()->attach([$roleResponsableProjet->id]);

                $this->command->info("✅ Compte Responsable projet créé avec succès pour {$ministere->nom}");
                $this->command->info("📧 Email: {$emailResponsableProjet}");
                $this->command->info("🔑 Mot de passe: {$passwordResponsable}");
            } else {
                $this->command->info("ℹ️ Le compte Responsable projet existe déjà pour {$ministere->nom}");
            }

            $this->command->info("✅ Espaces de travail créés avec succès pour {$ministere->nom} !");*/
        }
    }

    /**
     * Génère les initiales d'un nom d'organisation en excluant les mots communs
     *
     * Exemple: "Ministère du Plan et du Développement" → "MPD"
     *
     * @param string $nom Le nom de l'organisation
     * @return string Les initiales en majuscules
     */
    private function genererInitiales(string $nom): string
    {
        // Mots à exclure des initiales
        $motsExclus = ['du', 'de', 'la', 'le', 'des', 'et', 'en', 'l\'', 'd\''];

        // Nettoyer et découper le nom
        $mots = explode(' ', $nom);
        $initiales = '';

        foreach ($mots as $mot) {
            // Nettoyer le mot des apostrophes
            $motNettoye = str_replace(['\'', '\''], '', $mot);

            // Vérifier si le mot n'est pas dans la liste d'exclusion et n'est pas vide
            if (!in_array(strtolower($mot), $motsExclus) && !empty($motNettoye)) {
                $initiales .= strtoupper($motNettoye[0]);
            }
        }

        return $initiales;
    }
}
