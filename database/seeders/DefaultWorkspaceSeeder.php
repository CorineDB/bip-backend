<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Personne;
use App\Models\Organisation;
use App\Models\Dgpd;
use App\Models\Dpaf;
use App\Models\GroupeUtilisateur;
use App\Enums\EnumTypeOrganisation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DefaultWorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Création des espaces de travail par défaut...');


        /**
         * Créer l'instance DGPD
         */
        // Créer ou récupérer l'instance DGPD
        $dgpd = Dgpd::firstOrCreate(
            ['slug' => 'dgpd'],
            [
                'nom' => 'Direction Générale de la Programmation et de la Prospective pour le Développement',
                'description' => 'Direction en charge de la programmation et de la prospective pour le développement'
            ]
        );

        /**
         * Créer les rôles spécifiques à la DGPD
         */

        // Créer le rôle Analyste DGPD spécifique à cette instance DGPD
        $roleAnalysteDgpd = Role::firstOrCreate(
            [
                'slug' => 'analyste-dgpd',
                'roleable_type' => get_class($dgpd),
                'roleable_id' => $dgpd->id
            ],
            [
                'nom' => 'Analyste DGPD',
                'description' => 'Analyste de la Direction Générale de la Programmation et de la Prospective pour le Développement'
            ]
        );

        $this->command->info('✅ Rôle Analyste DGPD créé');

        /**
         * Créer le compte admin de la DGPD
         */

        // Récupérer le rôle DGPD
        $roleDgpd = Role::firstOrCreate(['slug' => 'dgpd'],['nom' => 'DGPD']);

        if (!$roleDgpd) {
            $this->command->error('⚠️  Le rôle DGPD n\'existe pas. Assurez-vous d\'exécuter PermissionSeeder avant ce seeder.');
            return;
        }

        // Vérifier si l'admin DGPD existe déjà
        $adminDgpd = User::where('email', 'admin@dgpd.bj')->first();

        if (!$adminDgpd) {
            // Créer la personne pour l'admin DGPD
            $adminDgpdPersonne = Personne::firstOrCreate(
                ['nom' => 'Admin', 'prenom' => 'DGPD'],
                [
                    'poste' => 'Administrateur DGPD',
                    'organismeId' => null
                ]
            );

            // Générer un mot de passe temporaire
            $password = 'DGPD123!';

            // Créer l'utilisateur admin DGPD
            $adminDgpd = User::create([
                'provider' => 'local',
                'provider_user_id' => 'admin@dgpd.bj',
                'username' => 'admin@dgpd.bj',
                'email' => 'admin@dgpd.bj',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'personneId' => $adminDgpdPersonne->id,
                'roleId' => $roleDgpd->id,
                'last_connection' => now(),
                'ip_address' => '127.0.0.1',
                'type' => 'dgpd',
                'profilable_id' => $dgpd->id,
                'profilable_type' => get_class($dgpd),
                'account_verification_request_sent_at' => Carbon::now(),
                'token' => str_replace(['/', '\\', '.'], '', Hash::make($dgpd->id . Hash::make('admin@dgpd.bj') . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                'link_is_valide' => true,
                'created_at' => now(),
                'lastRequest' => now()
            ]);

            // Attacher le rôle à l'utilisateur
            $adminDgpd->roles()->attach([$roleDgpd->id]);

            $this->command->info('✅ Compte DGPD créé avec succès !');
            $this->command->info('📧 Email: admin@dgpd.bj');
            $this->command->info('🔑 Mot de passe: ' . $password);
        } else {
            $this->command->info('ℹ️  Le compte admin DGPD existe déjà');
        }

        /**
         * Créer un utilisateur Analyste DGPD
         */

        // Vérifier si l'analyste DGPD existe déjà
        $analyteDgpd = User::where('email', 'analyste@dgpd.bj')->first();

        if (!$analyteDgpd) {
            // Créer la personne pour l'analyste DGPD
            $analystePersonne = Personne::firstOrCreate(
                ['nom' => 'Analyste', 'prenom' => 'DGPD'],
                [
                    'poste' => 'Analyste DGPD',
                    'organismeId' => null
                ]
            );

            // Générer un mot de passe temporaire
            $passwordAnalyste = 'Analyste123!';

            // Créer l'utilisateur analyste DGPD
            $analyteDgpd = User::create([
                'provider' => 'local',
                'provider_user_id' => 'analyste@dgpd.bj',
                'username' => 'analyste@dgpd.bj',
                'email' => 'analyste@dgpd.bj',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($passwordAnalyste),
                'personneId' => $analystePersonne->id,
                'roleId' => $roleAnalysteDgpd->id,
                'last_connection' => now(),
                'ip_address' => '127.0.0.1',
                'type' => 'analyste-dgpd',
                'profilable_id' => $dgpd->id,
                'profilable_type' => get_class($dgpd),
                'account_verification_request_sent_at' => Carbon::now(),
                'token' => str_replace(['/', '\\', '.'], '', Hash::make($dgpd->id . Hash::make('analyste@dgpd.bj') . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                'link_is_valide' => true,
                'created_at' => now(),
                'lastRequest' => now()
            ]);

            // Attacher le rôle à l'utilisateur
            $analyteDgpd->roles()->attach([$roleAnalysteDgpd->id]);

            $this->command->info('✅ Compte Analyste DGPD créé avec succès !');
            $this->command->info('📧 Email: analyste@dgpd.bj');
            $this->command->info('🔑 Mot de passe: ' . $passwordAnalyste);
        } else {
            $this->command->info('ℹ️  Le compte Analyste DGPD existe déjà');
        }

        // Créer le rôle Chargé d'étude spécifique à cette instance DGPD
        $roleChargeEtude = Role::firstOrCreate(
            [
                'slug' => 'charge-etude',
                'roleable_type' => get_class($dgpd),
                'roleable_id' => $dgpd->id
            ],
            [
                'nom' => 'Chargé d\'étude',
                'description' => 'Chargé d\'étude de la Direction Générale de la Programmation et de la Prospective pour le Développement'
            ]
        );

        $this->command->info('✅ Rôle Chargé d\'étude créé');

        /**
         * Créer les groupes spécifiques à la DGPD
         */

        // Créer le groupe Service technique/Service étude
        $groupeServiceTechnique = GroupeUtilisateur::firstOrCreate(
            [
                'slug' => 'service-technique-service-etude',
                'profilable_type' => get_class($dgpd),
                'profilable_id' => $dgpd->id
            ],
            [
                'nom' => 'Service technique/Service étude',
                'description' => 'Groupe du service technique et du service étude de la DGPD'
            ]
        );

        // Attacher les rôles au groupe (Analyste DGPD et Chargé d'étude)
        $groupeServiceTechnique->roles()->syncWithoutDetaching([
            $roleChargeEtude->id
        ]);

        $this->command->info('✅ Groupe Service technique/Service étude créé et associé aux rôles');

        /**
         * Créer une organisation de type ministère
         */

        // Créer l'organisation ministère
        $ministere = Organisation::firstOrCreate(
            ['slug' => 'ministere-planification-developpement'],
            [
                'nom' => 'Ministère du Plan et du Développement',
                'description' => 'Ministère chargé de la planification et du développement',
                'type' => EnumTypeOrganisation::MINISTERE,
                'parentId' => null
            ]
        );

        /**
         * Créer le compte admin du ministère
         */

        // Récupérer le rôle Organisation
        $roleOrganisation = Role::firstOrCreate(['slug' => 'organisation'],['nom' => 'Organisation']);

        if (!$roleOrganisation) {
            $this->command->error('⚠️  Le rôle Organisation n\'existe pas. Assurez-vous d\'exécuter PermissionSeeder avant ce seeder.');
            return;
        }

        // Vérifier si l'admin ministère existe déjà
        $adminMinistere = User::where('email', 'admin@ministere.bj')->first();

        if (!$adminMinistere) {
            // Créer la personne pour l'admin ministère
            $adminMinisterePersonne = Personne::firstOrCreate(
                ['nom' => 'Admin', 'prenom' => 'Ministère'],
                [
                    'poste' => 'Administrateur Ministère',
                    'organismeId' => $ministere->id
                ]
            );

            // Générer un mot de passe temporaire
            $passwordMinistere = 'Ministere123!';

            // Créer l'utilisateur admin ministère
            $adminMinistere = User::create([
                'provider' => 'local',
                'provider_user_id' => 'admin@ministere.bj',
                'username' => 'admin@ministere.bj',
                'email' => 'admin@ministere.bj',
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
                'token' => str_replace(['/', '\\', '.'], '', Hash::make($ministere->id . Hash::make('admin@ministere.bj') . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                'link_is_valide' => true,
                'created_at' => now(),
                'lastRequest' => now()
            ]);

            // Attacher le rôle à l'utilisateur
            $adminMinistere->roles()->attach([$roleOrganisation->id]);

            $this->command->info('✅ Organisation ministère créée avec succès !');
            $this->command->info('📧 Email: admin@ministere.bj');
            $this->command->info('🔑 Mot de passe: ' . $passwordMinistere);
        } else {
            $this->command->info('ℹ️  Le compte admin ministère existe déjà');
        }

        /**
         * Créer le groupe Comité de validation Ministériel du ministère
         */

        // Créer le groupe Comité de validation Ministériel
        $groupeComiteValidation = GroupeUtilisateur::firstOrCreate(
            [
                'slug' => 'comite-validation-ministeriel',
                'profilable_type' => get_class($ministere),
                'profilable_id' => $ministere->id
            ],
            [
                'nom' => 'Comité de validation Ministériel',
                'description' => 'Comité de validation ministériel chargé de l\'examen et de la validation des projets'
            ]
        );

        $this->command->info('✅ Groupe Comité de validation Ministériel créé');

        /**
         * Créer la DPAF du ministère
         */

        // Créer l'instance DPAF rattachée au ministère
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

        /**
         * Créer le compte admin de la DPAF
         */

        // Récupérer le rôle DPAF
        $roleDpaf = Role::firstOrCreate(['slug' => 'dpaf'], ['nom' => 'DPAF']);

        if (!$roleDpaf) {
            $this->command->error('⚠️  Le rôle DPAF n\'existe pas. Assurez-vous d\'exécuter PermissionSeeder avant ce seeder.');
            return;
        }

        // Vérifier si l'admin DPAF existe déjà
        $adminDpaf = User::where('email', 'admin@dpaf.bj')->first();

        if (!$adminDpaf) {
            // Créer la personne pour l'admin DPAF
            $adminDpafPersonne = Personne::firstOrCreate(
                ['nom' => 'Admin', 'prenom' => 'DPAF'],
                [
                    'poste' => 'Administrateur DPAF',
                    'organismeId' => $ministere->id
                ]
            );

            // Générer un mot de passe temporaire
            $passwordDpaf = 'DPAF123!';

            // Créer l'utilisateur admin DPAF
            $adminDpaf = User::create([
                'provider' => 'local',
                'provider_user_id' => 'admin@dpaf.bj',
                'username' => 'admin@dpaf.bj',
                'email' => 'admin@dpaf.bj',
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
                'token' => str_replace(['/', '\\', '.'], '', Hash::make($dpaf->id . Hash::make('admin@dpaf.bj') . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                'link_is_valide' => true,
                'created_at' => now(),
                'lastRequest' => now()
            ]);

            // Attacher le rôle à l'utilisateur
            $adminDpaf->roles()->attach([$roleDpaf->id]);

            $this->command->info('✅ DPAF créée avec succès !');
            $this->command->info('📧 Email: admin@dpaf.bj');
            $this->command->info('🔑 Mot de passe: ' . $passwordDpaf);
        } else {
            $this->command->info('ℹ️  Le compte admin DPAF existe déjà');
        }

        /**
         * Créer le rôle Responsable projet du ministère
         */

        // Créer le rôle Responsable projet spécifique au ministère
        $roleResponsableProjet = Role::firstOrCreate(
            [
                'slug' => 'responsable-projet',
                'roleable_type' => get_class($ministere),
                'roleable_id' => $ministere->id
            ],
            [
                'nom' => 'Responsable projet',
                'slug' => 'responsable-projet',
                'description' => 'Responsable de projet du ' . $ministere->nom,
                'roleable_type' => get_class($ministere),
                'roleable_id' => $ministere->id
            ]
        );

        $roleResponsableProjet->permissions()->sync([
            // Gestion des utilisateurs
            "voir-la-liste-des-utilisateurs",

            // Gestion des groupes-utilisateur
            "voir-la-liste-des-groupes-utilisateur",

            // Gestion des rôles et permissions
            "voir-la-liste-des-roles",

            "voir-la-dpaf",

            // Gestion les odds
            "voir-la-liste-des-odds",

            // Gestion les cibles
            "voir-la-liste-des-cibles",

            "voir-la-liste-des-departements",

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

            "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "effectuer-evaluation-climatique-idee-projet", "acceder-au-tableau-de-bord-climatique", "valider-le-score-d-analyse-climatique-interne-d-une-idee-de-projet",

            "acceder-au-tableau-d-amc", "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",

            // Gestion les notes conceptuelle
            "voir-la-liste-des-notes-conceptuelle", "commenter-une-note-conceptuelle", "voir-la-liste-des-commentaires-d-une-note-conceptuelle", "imprimer-une-note-conceptuelle", "voir-les-documents-relatifs-a-une-note-conceptuelle", "telecharger-les-documents-relatifs-a-une-note-conceptuelle",

            "voir-le-resultats-d-evaluation-d-une-note-conceptuelle", "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle",



            // TDRs
            "voir-la-liste-des-tdrs-de-prefaisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-prefaisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-prefaisabilite",
            "telecharger-un-rapport-de-prefaisabilite", "voir-la-liste-des-rapports-de-prefaisabilite",

            "voir-la-liste-des-tdrs-de-faisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-faisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-faisabilite",
            "soumettre-un-rapport-de-faisabilite","modifier-un-rapport-de-faisabilite", "supprimer-un-rapport-de-faisabilite", "telecharger-un-rapport-de-faisabilite", "voir-la-liste-des-rapports-de-faisabilite",

            "telecharger-un-rapport-d-evaluation-ex-ante", "voir-la-liste-des-rapports-d-evaluation-ex-ante",

            // Commentaires et fichiers
            "ajouter-commentaire", "voir-commentaires", "modifier-commentaire", "supprimer-commentaire",
            "telecharger-fichier", "upload-fichier", "supprimer-fichier",
        ]);

        $this->command->info('✅ Rôle Responsable projet créé');

        /**
         * Créer un utilisateur Responsable projet
         */

        // Vérifier si le responsable projet existe déjà
        $responsableProjet = User::where('email', 'responsable-projet@ministere.bj')->first();

        if (!$responsableProjet) {
            // Créer la personne pour le responsable projet
            $responsableProjetPersonne = Personne::firstOrCreate(
                ['nom' => 'Responsable', 'prenom' => 'Projet'],
                [
                    'poste' => 'Responsable de projet',
                    'organismeId' => $ministere->id
                ]
            );

            // Générer un mot de passe temporaire
            $passwordResponsable = 'ResponsableProjet123!';

            // Créer l'utilisateur responsable projet
            $responsableProjet = User::create([
                'provider' => 'local',
                'provider_user_id' => 'responsable-projet@ministere.bj',
                'username' => 'responsable-projet@ministere.bj',
                'email' => 'responsable-projet@ministere.bj',
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
                'token' => str_replace(['/', '\\', '.'], '', Hash::make($ministere->id . Hash::make('responsable-projet@ministere.bj') . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                'link_is_valide' => true,
                'created_at' => now(),
                'lastRequest' => now()
            ]);

            // Attacher le rôle à l'utilisateur
            $responsableProjet->roles()->attach([$roleResponsableProjet->id]);

            $this->command->info('✅ Compte Responsable projet créé avec succès !');
            $this->command->info('📧 Email: responsable-projet@ministere.bj');
            $this->command->info('🔑 Mot de passe: ' . $passwordResponsable);
        } else {
            $this->command->info('ℹ️  Le compte Responsable projet existe déjà');
        }

        /**
         * Créer le rôle Responsable hiérarchique du ministère
         */

        // Créer le rôle Responsable hiérarchique spécifique au ministère
        $roleResponsableHierarchique = Role::firstOrCreate(
            [
                'slug' => 'responsable-hierarchique',
                'roleable_type' => get_class($ministere),
                'roleable_id' => $ministere->id
            ],
            [
                'nom' => 'Responsable hiérarchique',
                'description' => 'Responsable hiérarchique du ' . $ministere->nom
            ]
        );

        $roleResponsableHierarchique->permissions()->sync([
            // Gestion des utilisateurs
            "voir-la-liste-des-utilisateurs",

            // Gestion des groupes-utilisateur
            "voir-la-liste-des-groupes-utilisateur",

            // Gestion des rôles et permissions
            "voir-la-liste-des-roles",

            "voir-la-dpaf",

            // Gestion les odds
            "voir-la-liste-des-odds",

            // Gestion les cibles
            "voir-la-liste-des-cibles",

            "voir-la-liste-des-departements",

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
            "voir-la-liste-des-idees-de-projet", "valider-une-idee-de-projet-en-interne", "telecharger-la-fiche-synthese-une-idee-de-projet",

            "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet", "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet", "acceder-au-tableau-de-bord-climatique",

            "acceder-au-tableau-d-amc", "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",

            // Gestion les notes conceptuelle
            "voir-la-liste-des-notes-conceptuelle", "commenter-une-note-conceptuelle", "voir-la-liste-des-commentaires-d-une-note-conceptuelle", "imprimer-une-note-conceptuelle", "voir-les-documents-relatifs-a-une-note-conceptuelle", "telecharger-les-documents-relatifs-a-une-note-conceptuelle",

            "voir-le-resultats-d-evaluation-d-une-note-conceptuelle", "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle",

            // TDRs
            "voir-la-liste-des-tdrs-de-prefaisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-prefaisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-prefaisabilite",
            "telecharger-un-rapport-de-prefaisabilite", "voir-la-liste-des-rapports-de-prefaisabilite",

            "voir-la-liste-des-tdrs-de-faisabilite", "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-faisabilite", "telecharger-un-fichier-complementaire-d-un-tdr-de-faisabilite",
            "telecharger-un-rapport-de-faisabilite", "voir-la-liste-des-rapports-de-faisabilite",

            "telecharger-un-rapport-d-evaluation-ex-ante", "voir-la-liste-des-rapports-d-evaluation-ex-ante",

            // Commentaires et fichiers
            "ajouter-commentaire", "voir-commentaires", "modifier-commentaire", "supprimer-commentaire",
            "telecharger-fichier", "upload-fichier", "supprimer-fichier",
        ]);

        $this->command->info('✅ Rôle Responsable hiérarchique créé');

        /**
         * Créer un utilisateur Responsable hiérarchique
         */

        // Vérifier si le responsable hiérarchique existe déjà
        $responsableHierarchique = User::where('email', 'responsable-hierarchique@ministere.bj')->first();

        if (!$responsableHierarchique) {
            // Créer la personne pour le responsable hiérarchique
            $responsableHierarchiquePersonne = Personne::firstOrCreate(
                ['nom' => 'Responsable', 'prenom' => 'Hiérarchique'],
                [
                    'poste' => 'Responsable hiérarchique',
                    'organismeId' => $ministere->id
                ]
            );

            // Générer un mot de passe temporaire
            $passwordResponsableHier = 'ResponsableHier123!';

            // Créer l'utilisateur responsable hiérarchique
            $responsableHierarchique = User::create([
                'provider' => 'local',
                'provider_user_id' => 'responsable-hierarchique@ministere.bj',
                'username' => 'responsable-hierarchique@ministere.bj',
                'email' => 'responsable-hierarchique@ministere.bj',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($passwordResponsableHier),
                'personneId' => $responsableHierarchiquePersonne->id,
                'roleId' => $roleResponsableHierarchique->id,
                'last_connection' => now(),
                'ip_address' => '127.0.0.1',
                'type' => 'responsable-hierarchique',
                'profilable_id' => $ministere->id,
                'profilable_type' => get_class($ministere),
                'account_verification_request_sent_at' => Carbon::now(),
                'token' => str_replace(['/', '\\', '.'], '', Hash::make($ministere->id . Hash::make('responsable-hierarchique@ministere.bj') . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                'link_is_valide' => true,
                'created_at' => now(),
                'lastRequest' => now()
            ]);

            // Attacher le rôle à l'utilisateur
            $responsableHierarchique->roles()->attach([$roleResponsableHierarchique->id]);

            $this->command->info('✅ Compte Responsable hiérarchique créé avec succès !');
            $this->command->info('📧 Email: responsable-hierarchique@ministere.bj');
            $this->command->info('🔑 Mot de passe: ' . $passwordResponsableHier);
        } else {
            $this->command->info('ℹ️  Le compte Responsable hiérarchique existe déjà');
        }

        $this->command->info('✅ Espaces de travail créés avec succès !');
    }
}