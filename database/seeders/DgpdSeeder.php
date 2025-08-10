<?php

namespace Database\Seeders;

use App\Models\Dgpd;
use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Personne;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DgpdSeeder extends Seeder
{
    // Liste des permissions pour le rôle analyste DGPD
    protected $analystePermissionsSlugs = [
        "voir-la-liste-des-utilisateurs",
        "voir-la-liste-des-groupes-utilisateur",
        "voir-la-liste-des-roles",
        "voir-la-liste-des-odds",
        "voir-la-liste-des-cibles",
        "voir-la-liste-des-departements",
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
        "voir-la-liste-des-idees-de-projet",
        "analyser-les-donnees-projet",
        "generer-rapports-statistiques",
        "acceder-au-tableau-de-bord-climatique",
        "acceder-au-tableau-d-amc",
        "consulter-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",
        "imprimer-la-grille-d-analyse-multi-critere-d-une-idee-de-projet",
        "effectuer-l-analyse-climatique-d-une-idee-de-projet",
        "acceder-au-tableau-d-amc",
        "effectuer-l-amc-d-une-idee-de-projet",
        "valider-une-idee-de-projet-a-projet",
        "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",
        "voir-la-liste-des-notes-conceptuelle",
        "voir-la-liste-des-commentaires-d-une-note-conceptuelle",
        "imprimer-une-note-conceptuelle",
        "voir-les-documents-relatifs-a-une-note-conceptuelle",
        "telecharger-les-documents-relatifs-a-une-note-conceptuelle",
        "voir-le-resultats-d-evaluation-d-une-note-conceptuelle",
        "imprimer-le-resultats-d-evaluation-d-une-note-conceptuelle",
        "voir-la-liste-des-tdrs-de-prefaisabilite",
        "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-prefaisabilite",
        "telecharger-un-fichier-complementaire-d-un-tdr-de-prefaisabilite",
        "telecharger-un-rapport-de-prefaisabilite",
        "voir-la-liste-des-rapports-de-prefaisabilite",
        "voir-la-liste-des-tdrs-de-faisabilite",
        "voir-la-liste-des-fichiers-complementaires-d-un-tdr-de-faisabilite",
        "telecharger-un-fichier-complementaire-d-un-tdr-de-faisabilite",
        "telecharger-un-rapport-de-faisabilite",
        "voir-la-liste-des-rapports-de-faisabilite",
        "telecharger-un-rapport-d-evaluation-ex-ante",
        "voir-la-liste-des-rapports-d-evaluation-ex-ante",
        "telecharger-fichier",
        "upload-fichier",
    ];

    public function run(): void
    {
        // Créer l'organisation DGPD
        $dgpdOrganisation = Organisation::updateOrCreate(
            ['slug' => 'dgpd'],
            [
                'nom' => 'Direction Générale de la Programmation et du Développement',
                'slug' => 'dgpd',
                'description' => 'Direction centrale chargée de la programmation des investissements publics et du développement',
                'type' => 'etatique',
                'parentId' => null
            ]
        );

        // Créer l'entité DGPD
        $dgpd = Dgpd::firstOrCreate(
            ['slug' => 'dgpd'],
            [
                'nom' => 'Direction Générale de la Programmation et du Développement',
                'description' => 'Direction centrale responsable de la coordination et du suivi des projets de développement'
            ]
        );

        // --- ADMIN DGPD ---
        $roleAdmin = Role::firstOrCreate(['slug' => 'admin-dgpd'], [
            'nom' => 'Administrateur DGPD',
            'description' => 'Administrateur de la DGPD'
        ]);

        $adminEmail = 'celerite@gmail.com';
        $adminDgpd = User::where('email', $adminEmail)->first();

        if (!$adminDgpd) {
            $adminPersonne = Personne::firstOrCreate(
                ['nom' => 'Admin', 'prenom' => 'DGPD'],
                [
                    'poste' => 'Administrateur DGPD',
                    'organismeId' => $dgpdOrganisation->id
                ]
            );

            $passwordAdmin = 'AdminDGPD123!';

            $adminDgpd = User::create([
                'provider' => 'local',
                'provider_user_id' => $adminEmail,
                'username' => $adminEmail,
                'email' => $adminEmail,
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($passwordAdmin),
                'personneId' => $adminPersonne->id,
                'roleId' => $roleAdmin->id,
                'last_connection' => now(),
                'ip_address' => '127.0.0.1',
                'type' => 'admin-dgpd',
                'profilable_id' => $dgpd->id,
                'profilable_type' => get_class($dgpd),
                'account_verification_request_sent_at' => Carbon::now(),
                'token' => str_replace(['/', '\\', '.'], '', Hash::make($dgpd->id . Hash::make($adminEmail) . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                'link_is_valide' => true,
                'created_at' => now(),
                'lastRequest' => now()
            ]);

            $adminDgpd->roles()->attach([$roleAdmin->id]);

            $this->command->info("✅ Admin DGPD créé");
            $this->command->info("📧 Email : {$adminEmail}");
            $this->command->info("🔑 Mot de passe : {$passwordAdmin}");
        } else {
            $this->command->info("ℹ️ Le compte admin DGPD existe déjà");
        }

        // --- RÔLE ANALYSTE DGPD ---
        $roleAnalyste = Role::firstOrCreate(
            [
                'slug' => 'analyste-dgpd',
                'roleable_type' => get_class($dgpd),
                'roleable_id' => $dgpd->id,
            ],
            [
                'nom' => 'Analyste DGPD',
                'description' => 'Analyste en charge de l\'analyse des projets et programmes de développement',
                'roleable_type' => get_class($dgpd),
                'roleable_id' => $dgpd->id,
            ]
        );

        // Attacher les permissions au rôle analyste
        $permissionIds = Permission::whereIn('slug', $this->analystePermissionsSlugs)->pluck('id')->toArray();
        $roleAnalyste->permissions()->sync($permissionIds);

        $this->command->info("✅ Rôle Analyste DGPD créé avec permissions");

        // --- UTILISATEUR ANALYSTE DGPD ---
        $analysteEmail = 'cocorine999@gmail.com';
        $analyste = User::where('email', $analysteEmail)->first();

        if (!$analyste) {
            $analystePersonne = Personne::firstOrCreate(
                ['nom' => 'Analyste', 'prenom' => 'DGPD'],
                [
                    'poste' => 'Analyste Programmation et Développement',
                    'organismeId' => $dgpdOrganisation->id
                ]
            );

            $passwordAnalyste = 'AnalysteDGPD123!';

            $analyste = User::create([
                'provider' => 'local',
                'provider_user_id' => $analysteEmail,
                'username' => $analysteEmail,
                'email' => $analysteEmail,
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($passwordAnalyste),
                'personneId' => $analystePersonne->id,
                'roleId' => $roleAnalyste->id,
                'last_connection' => now(),
                'ip_address' => '127.0.0.1',
                'type' => 'analyste-dgpd',
                'profilable_id' => $dgpd->id,
                'profilable_type' => get_class($dgpd),
                'account_verification_request_sent_at' => Carbon::now(),
                'token' => str_replace(['/', '\\', '.'], '', Hash::make($dgpd->id . Hash::make($analysteEmail) . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                'link_is_valide' => true,
                'created_at' => now(),
                'lastRequest' => now()
            ]);

            $analyste->roles()->attach([$roleAnalyste->id]);

            $this->command->info("✅ Compte Analyste DGPD créé");
            $this->command->info("📧 Email : {$analysteEmail}");
            $this->command->info("🔑 Mot de passe : {$passwordAnalyste}");
        } else {
            $this->command->info("ℹ️ Le compte Analyste DGPD existe déjà");
        }

        $this->command->info("✅ DGPD configurée avec succès !");
    }
}