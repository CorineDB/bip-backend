
        $permissions_par_role = [
            "Organisation" => [
                // === A. GESTION ADMINISTRATIVE ===
                // Gestion des utilisateurs
                "gerer-les-utilisateurs", "voir-la-liste-des-utilisateurs", "creer-un-utilisateur",
                "modifier-un-utilisateur", "supprimer-un-utilisateur",

                // Gestion des groupes-utilisateur
                "gerer-les-groupes-utilisateur", "voir-la-liste-des-groupes-utilisateur",
                "creer-un-groupe-utilisateur", "modifier-un-groupe-utilisateur",
                "supprimer-un-groupe-utilisateur", "assigner-un-role-a-un-groupe-utilisateur",
                "retirer-un-role-a-un-groupe-utilisateur", "ajouter-un-utilisateur-a-un-groupe-utilisateur",
                "ajouter-nouvel-utilisateur-a-un-groupe-utilisateur",

                // Gestion des rôles et permissions
                "gerer-les-roles", "voir-la-liste-des-roles", "creer-un-role",
                "modifier-un-role", "supprimer-un-role", "assigner-des-permissions-a-un-role",
                "retirer-des-permissions-a-un-role",

                // === CONSULTATION ADMINISTRATIVE ===
                "gerer-la-dpaf", "voir-la-dpaf", "creer-la-dpaf", "modifier-la-dpaf", "supprimer-la-dpaf",
                "voir-la-liste-des-departements",

                // Gestion des organisations
                "gerer-les-organisations", "voir-la-liste-des-organisations", "creer-une-organisation", "modifier-une-organisation", "supprimer-une-organisation",

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

                // === CONSULTATION DES IDÉES DE PROJET ===
                "voir-la-liste-des-idees-de-projet",
                "consulter-une-idee-de-projet",
                "exporter-une-idee-de-projet",
                "imprimer-une-idee-de-projet",
                "commenter-une-idee-de-projet",
                "voir-les-commentaires-d-une-idee-de-projet",
                "voir-les-documents-d-une-idee-de-projet",
                "telecharger-les-documents-d-une-idee-de-projet",

                // Consultation des résultats d'évaluations (sans pouvoir effectuer/valider/obtenir scores/relancer)
                "exporter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",
                "commenter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",
                "exporter-le-resultats-de-l-analyse-d-une-idee-de-projet",
                "commenter-le-resultats-de-l-analyse-d-une-idee-de-projet",

                // AMC - Consultation uniquement
                "acceder-au-tableau-d-amc",
                "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",
                "commenter-le-resultats-de-l-amc-d-une-idee-de-projet",

                // Tableaux de bord - Consultation uniquement
                "acceder-au-tableau-de-bord-de-pertinence",
                "acceder-au-tableau-de-bord-climatique",

                // Canevas fiche idée
                "consulter-le-canevas-de-la-fiche-idee-de-projet",
                "remplir-le-canevas-de-la-fiche-idee-de-projet",
                "telecharger-la-fiche-synthese-une-idee-de-projet",

                // Grilles d'analyse - Consultation
                "consulter-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                "imprimer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet",
                "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet",
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
                "telecharger-documents",
            ]
