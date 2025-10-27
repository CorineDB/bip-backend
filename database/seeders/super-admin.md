

        $permissions_par_role = [
            // Administration Générale
            "Super Admin" => [
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

                // === B. CONFIGURATION ORGANISATIONNELLE ===

                // Gestion départements
                "gerer-les-departements", "voir-la-liste-des-departements", "creer-un-departement",
                "modifier-un-departement", "supprimer-un-departement",

                // Gestion DGPD
                "gerer-la-dgpd", "voir-la-dgpd", "creer-la-dgpd", "modifier-la-dgpd", "supprimer-la-dgpd",

                // Gestion des organisations
                "gerer-les-organisations", "voir-la-liste-des-organisations", "creer-une-organisation", "modifier-une-organisation", "supprimer-une-organisation",

                // Gestion DPAF
                "voir-la-dpaf",

                // === C. DONNÉES DE RÉFÉRENCE SYSTÈME ===
                // ODDs
                "gerer-les-odds", "voir-la-liste-des-odds", "creer-un-odd", "modifier-un-odd", "supprimer-un-odd",

                // Cibles
                "gerer-les-cibles", "voir-la-liste-des-cibles", "creer-une-cible",
                "modifier-une-cible", "supprimer-une-cible",

                // Entités géographiques
                "voir-les-departements-geo", "gerer-les-departements-geo",
                "voir-la-liste-des-communes", "gerer-les-communes",
                "voir-la-liste-des-arrondissements", "gerer-les-arrondissements",
                "voir-la-liste-des-villages", "gerer-les-villages",

                // Secteurs d'intervention
                "voir-la-liste-des-grands-secteurs", "voir-la-liste-des-secteurs",
                "voir-la-liste-des-sous-secteurs", "gerer-les-secteurs", "creer-un-secteur",
                "modifier-un-secteur", "supprimer-un-secteur",

                // Types d'intervention
                "voir-la-liste-des-types-intervention", "gerer-les-types-intervention",
                "creer-un-type-intervention", "modifier-un-type-intervention", "supprimer-un-type-intervention",

                // Financements
                "voir-la-liste-des-types-financement", "voir-la-liste-des-natures-financement",
                "voir-la-liste-des-sources-financement", "gerer-les-financements",
                "creer-un-financement", "modifier-un-financement", "supprimer-un-financement",

                // Programmes
                "voir-la-liste-des-programmes", "voir-la-liste-des-composants-programme",
                "gerer-un-programme", "creer-un-programme", "modifier-un-programme", "supprimer-un-programme",
                "gerer-les-composants-de-programme", "creer-un-composant-de-programme",
                "modifier-un-composant-de-programme", "supprimer-un-composant-de-programme",

                // Cadres stratégiques (consultation)
                "voir-la-liste-des-axes-du-pag", "voir-la-liste-des-piliers-du-pag",
                "voir-la-liste-des-actions-du-pag", "voir-la-liste-des-orientations-strategique-du-pnd",
                "voir-la-liste-des-objectifs-strategique-du-pnd", "voir-la-liste-des-resultats-strategique-du-pnd",

                // Catégories de projet
                "voir-la-liste-des-categories-de-projet", "gerer-les-categories-de-projet",
                "creer-une-categorie-de-projet", "modifier-une-categorie-de-projet",
                "supprimer-une-categorie-de-projet",

                // === D. GESTION DES CANEVAS ET OUTILS SYSTÈME ===
                // Canevas généraux
                "voir-la-liste-des-canevas", "gerer-les-canevas", "creer-un-canevas",
                "modifier-un-canevas", "supprimer-un-canevas", "imprimer-un-canevas",

                // Canevas fiche idée de projet
                "creer-le-canevas-de-la-fiche-idee-de-projet", "modifier-le-canevas-de-la-fiche-idee-de-projet",
                "consulter-le-canevas-de-la-fiche-idee-de-projet",

                // Canevas rédaction note conceptuelle
                "creer-la-fiche-de-redaction-d-une-note-conceptuelle", "modifier-la-fiche-de-redaction-d-une-note-conceptuelle", 
                "creer-le-canevas-de-redaction-note-conceptuelle",
                "modifier-le-canevas-de-redaction-note-conceptuelle",
                "consulter-le-canevas-de-redaction-note-conceptuelle",
                "imprimer-le-canevas-de-redaction-note-conceptuelle",
                "exporter-le-canevas-de-redaction-note-conceptuelle",
                "telecharger-le-canevas-de-redaction-note-conceptuelle",
                "restaurer-version-anterieure-canevas-note-conceptuelle",
                "voir-historique-canevas-note-conceptuelle",

                // Canevas d'appréciation TDR
                "creer-le-canevas-d-appreciation-d-un-tdr", "modifier-le-canevas-d-appreciation-d-un-tdr",
                "consulter-le-canevas-d-appreciation-d-un-tdr", "imprimer-le-canevas-d-appreciation-d-un-tdr",

                // Grilles d'analyse - Pertinence
                "creer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                "modifier-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                "consulter-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",
                "imprimer-la-grille-d-analyse-de-pertinence-d-une-idee-de-projet",

                // Grilles d'analyse - Climatique
                "creer-la-grille-d-analyse-climatique-d-une-idee-de-projet",
                "modifier-la-grille-d-analyse-climatique-d-une-idee-de-projet",
                "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet",
                "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet",

                // Grilles d'analyse - AMC
                "creer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",
                "modifier-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",
                "consulter-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",
                "imprimer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",

                // Outil d'analyse note conceptuelle
                "creer-l-outil-d-analyse-d-une-note-conceptuelle",
                "modifier-l-outil-d-analyse-d-une-note-conceptuelle",
                "consulter-l-outil-d-analyse-d-une-note-conceptuelle",
                "imprimer-l-outil-d-analyse-d-une-note-conceptuelle",
                "creer-l-outil-d-analyse-d-une-note-conceptuelle",
                "modifier-l-outil-d-analyse-d-une-note-conceptuelle", 

                // Outils d'évaluation - Pertinence (instances uniques)
                "creer-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
                "modifier-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
                "consulter-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
                "imprimer-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
                "exporter-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
                "telecharger-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
                "restaurer-version-anterieure-de-l-outil-d-evaluation-de-la-pertinence-des-idees-de-projet",
                "voir-historique-grille-evaluation-de-la-pertinence-des-idees-de-projet",

                // Outils d'évaluation - Climatique
                "creer-l-outil-d-evaluation-climatique-des-idees-de-projet",
                "modifier-l-outil-d-evaluation-climatique-des-idees-de-projet",
                "consulter-l-outil-d-evaluation-climatique-des-idees-de-projet",
                "imprimer-l-outil-d-evaluation-climatique-des-idees-de-projet",
                "exporter-l-outil-d-evaluation-climatique-des-idees-de-projet",
                "telecharger-l-outil-d-evaluation-climatique-des-idees-de-projet",
                "voir-historique-de-l-outil-d-evaluation-climatique-des-idees-de-projet",

                // Outils d'évaluation - AMC
                "creer-l-outil-d-analyse-multicritere-des-idees-de-projet",
                "modifier-l-outil-d-analyse-multicritere-des-idees-de-projet",
                "consulter-l-outil-d-analyse-multicritere-des-idees-de-projet",
                "imprimer-l-outil-d-analyse-multicritere-des-idees-de-projet",
                "exporter-l-outil-d-analyse-multicritere-des-idees-de-projet",
                "telecharger--l-outil-d-analyse-multicritere-des-idees-de-projet",
                "voir-historique-outil-d-analyse-multicritere-des-idees-de-projet",

                // Checklists d'appréciation - TDR Préfaisabilité
                "creer-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
                "modifier-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
                "consulter-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
                "imprimer-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
                "exporter-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
                "telecharger-le-check-liste-d-appreciation-des-tdrs-de-prefaisabilite",
                "restaurer-version-anterieure-checklist-appreciation-tdr-prefaisabilite",

                // Checklists d'appréciation - TDR Faisabilité
                "creer-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
                "modifier-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
                "consulter-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
                "imprimer-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
                "exporter-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
                "telecharger-le-check-liste-d-appreciation-des-tdrs-de-faisabilite",
                "restaurer-version-anterieure-checklist-appreciation-tdr-faisabilite",

                // Checklists d'appréciation - Notes Conceptuelles
                "creer-le-check-liste-d-appreciation-des-notes-conceptuelle",
                "modifier-le-check-liste-d-appreciation-des-notes-conceptuelle",
                "consulter-le-check-liste-d-appreciation-des-notes-conceptuelle",
                "imprimer-le-check-liste-d-appreciation-des-notes-conceptuelle",
                "exporter-le-check-liste-d-appreciation-des-notes-conceptuelle",
                "telecharger-le-check-liste-d-appreciation-des-notes-conceptuelle",
                "restaurer-version-anterieure-checklist-des-notes-conceptuelle",

                // Checklists de suivi - Rapports Préfaisabilité
                "creer-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
                "modifier-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
                "consulter-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
                "imprimer-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
                "exporter-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
                "telecharger-le-check-liste-de-suivi-des-rapports-de-prefaisabilite",
                "restaurer-version-anterieure-checklist-suivi-rapport-prefaisabilite",

                // Checklists de suivi - Faisabilité Technique
                "creer-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
                "modifier-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
                "consulter-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
                "imprimer-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
                "exporter-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",
                "telecharger-le-check-liste-de-suivi-des-etudes-de-faisabilite-technique",

                // Checklists de suivi - Faisabilité Économique
                "creer-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
                "modifier-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
                "consulter-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
                "imprimer-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
                "exporter-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",
                "telecharger-le-check-liste-de-suivi-des-etudes-de-faisabilite-economique",

                // Checklists de suivi - Faisabilité Marché
                "creer-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
                "modifier-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
                "consulter-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
                "imprimer-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
                "exporter-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",
                "telecharger-le-check-liste-de-suivi-des-etudes-de-faisabilite-marche",

                // Checklists de suivi - Faisabilité Organisationnelle et Juridique
                "creer-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
                "modifier-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
                "consulter-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
                "imprimer-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
                "exporter-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",
                "telecharger-le-check-liste-de-suivi-des-etudes-de-faisabilite-organisationnelle-juridique",

                // Checklists de suivi - Impact Environnemental et Social
                "creer-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
                "modifier-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
                "consulter-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
                "imprimer-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
                "exporter-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",
                "telecharger-le-check-liste-de-suivi-des-etudes-d-analyse-d-impact-environnemental-sociale",

                // Checklists de suivi - Faisabilité Financière
                "creer-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
                "modifier-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
                "consulter-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
                "imprimer-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
                "exporter-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",
                "telecharger-le-check-liste-de-suivi-des-etudes-d-analyse-de-la-faisabilite-financiere",

                // Checklists - Assurance Qualité
                "creer-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
                "modifier-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
                "consulter-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
                "imprimer-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
                "exporter-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",
                "telecharger-le-check-liste-de-suivi-pour-l-assurance-qualite-des-rapports-d-etude-de-faisabilite",

                // Checklists - Contrôle Qualité
                "creer-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
                "modifier-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
                "consulter-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
                "imprimer-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
                "exporter-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",
                "telecharger-le-check-liste-de-suivi-du-controle-qualite-des-rapports-d-etude-de-faisabilite-preliminaire",

                // === E. VISUALISATION DES PROJETS OPÉRATIONNELS (Consultation uniquement) ===
                // Idées de projet - Consultation
                "voir-la-liste-des-idees-de-projet", "consulter-une-idee-de-projet",
                "voir-les-commentaires-d-une-idee-de-projet", "voir-les-documents-d-une-idee-de-projet",
                "telecharger-les-documents-d-une-idee-de-projet", "exporter-une-idee-de-projet", "imprimer-une-idee-de-projet",

                // Canevas fiche idée (consultation et remplissage en lecture seule)
                "consulter-le-canevas-de-la-fiche-idee-de-projet", "telecharger-la-fiche-synthese-une-idee-de-projet",

                // Grilles d'analyse - Consultation des résultats
                "acceder-au-tableau-de-bord-de-pertinence", "acceder-au-tableau-de-bord-climatique", "acceder-au-tableau-d-amc",
                "exporter-le-resultats-de-l-analyse-d-une-idee-de-projet",
                "exporter-le-resultats-de-l-evaluation-climatique-d-une-idee-de-projet",
                "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",

                // Projets - Consultation
                "voir-la-liste-des-projets", "consulter-un-projet", "exporter-un-projet", "imprimer-un-projet",
                "voir-les-commentaires-d-un-projet", "voir-les-documents-d-un-projet",
                "telecharger-les-documents-d-un-projet", "voir-historique-projet", "generer-rapport-projet",

                // Notes conceptuelles - Consultation
                "voir-la-liste-des-notes-conceptuelle", "voir-la-liste-des-commentaires-d-une-note-conceptuelle",
                "imprimer-une-note-conceptuelle", "voir-les-documents-relatifs-a-une-note-conceptuelle",
                "telecharger-les-documents-relatifs-a-une-note-conceptuelle",
                "consulter-la-fiche-de-redaction-d-une-note-conceptuelle",
                "imprimer-la-fiche-de-redaction-d-une-note-conceptuelle",
                "voir-le-resultats-d-evaluation-d-une-note-conceptuelle",
                "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle",
                "consulter-les-details-de-la-validation-de-l-etude-de-profil",

                // TDRs - Consultation
                "voir-la-liste-des-tdrs-de-prefaisabilite", "consulter-le-details-d-appreciation-d-un-tdr-de-prefaisabilite",
                "voir-details-de-l-appreciation-un-tdr-de-prefaisabilite",

                "voir-la-liste-des-tdrs-de-faisabilite", "consulter-le-details-d-appreciation-d-un-tdr-de-faisabilite",
                "voir-details-de-l-appreciation-un-tdr-de-faisabilite",

                "voir-tdr-prefaisabilite", "voir-tdr-faisabilite",
                "telecharger-tdr-prefaisabilite", "telecharger-tdr-faisabilite",

                // Rapports - Consultation
                "voir-la-liste-des-rapports-de-faisabilite-preliminaire",

                "voir-la-liste-des-rapports-de-prefaisabilite",
                "consulter-les-details-de-la-validation-de-l-etude-de-prefaisabilite",

                "voir-la-liste-des-rapports-de-faisabilite",
                "consulter-les-details-de-la-validation-de-l-etude-de-faisabilite",

                // Évaluation Ex-Ante - Consultation
                "voir-la-liste-des-rapports-d-evaluation-ex-ante", "consulter-un-rapport-d-evaluation-ex-ante",
                "consulter-les-details-de-la-validation-de-la-validation-finale",
                "voir-les-documents-annexes-d-un-rapport-d-evaluation-ex-ante",
                "voir-historique-rapport-d-evaluation-ex-ante",

                // Documents et fichiers - Consultation
                "voir-commentaires", "telecharger-fichier", "consulter-un-fichier", "telecharger-un-fichier",
                "telecharger-documents", "voir-la-liste-des-canevas",
                "telecharger-un-canevas-analyse-idee", "voir-le-canevas-de-la-fiche-idee",
                "telecharger-canevas-fiche-idee",
            ],
