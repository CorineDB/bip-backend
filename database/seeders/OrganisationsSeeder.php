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
        "voir-la-liste-des-utilisateurs",
        "voir-la-liste-des-groupes-utilisateur",
        "voir-la-liste-des-roles",
        "voir-la-dpaf",
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
        "gerer-les-idees-projet",
        "creer-une-idee-de-projet",
        "modifier-une-idee-de-projet",
        "supprimer-une-idee-de-projet",
        "effectuer-evaluation-climatique-idee-projet",
        "obtenir-score-climatique",
        "obtenir-score-climatique-une-projet",
        "valider-le-score-climatique-une-idee-de-projet",
        "relancer-l-evaluation-climatique-une-idee-de-projet",
        "consulter-le-canevas-de-la-fiche-idee-de-projet",
        "remplir-le-canevas-de-la-fiche-idee-de-projet",
        "telecharger-la-fiche-synthese-une-idee-de-projet",
        "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet",
        "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet",
        "effectuer-evaluation-climatique-idee-projet",
        "acceder-au-tableau-de-bord-climatique",
        "valider-le-score-d-analyse-climatique-interne-d-une-idee-de-projet",
        "acceder-au-tableau-d-amc",
        "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",
        "voir-la-liste-des-notes-conceptuelle",
        "commenter-une-note-conceptuelle",
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
        "soumettre-un-rapport-de-faisabilite",
        "modifier-un-rapport-de-faisabilite",
        "supprimer-un-rapport-de-faisabilite",
        "telecharger-un-rapport-de-faisabilite",
        "voir-la-liste-des-rapports-de-faisabilite",
        "telecharger-un-rapport-d-evaluation-ex-ante",
        "voir-la-liste-des-rapports-d-evaluation-ex-ante",
        "ajouter-commentaire",
        "voir-commentaires",
        "modifier-commentaire",
        "supprimer-commentaire",
        "telecharger-fichier",
        "upload-fichier",
        "supprimer-fichier",
    ];

    protected $responsableHierarchiquePermissionsSlugs = [
        // permissions (idem, voir ton exemple)
        "voir-la-liste-des-utilisateurs",
        "voir-la-liste-des-groupes-utilisateur",
        "voir-la-liste-des-roles",
        "voir-la-dpaf",
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
        "valider-une-idee-de-projet-en-interne",
        "telecharger-la-fiche-synthese-une-idee-de-projet",
        "consulter-la-grille-d-analyse-climatique-d-une-idee-de-projet",
        "imprimer-la-grille-d-analyse-climatique-d-une-idee-de-projet",
        "acceder-au-tableau-de-bord-climatique",
        "acceder-au-tableau-d-amc",
        "imprimer-le-resultats-de-l-amc-d-une-idee-de-projet",
        "voir-la-liste-des-notes-conceptuelle",
        "commenter-une-note-conceptuelle",
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
        "ajouter-commentaire",
        "voir-commentaires",
        "modifier-commentaire",
        "supprimer-commentaire",
        "telecharger-fichier",
        "upload-fichier",
        "supprimer-fichier",
    ];

    public function run(): void
    {
        $organisations = [
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
        ];
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
            ],
            // Ajoute autant que nécessaire...
        ];

        foreach ($organisations as $organisation) {
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
        }

        // Récupérer le rôle Organisation
        $roleOrganisation = Role::firstOrCreate(['slug' => 'organisation'], ['nom' => 'Organisation']);

        foreach ($ministeres as $ministereData) {
            $enfants = $ministereData['enfants'] ?? [];
            unset($ministereData['enfants']);

            $ministere = Organisation::updateOrCreate(
                ['slug' => $ministereData['slug']],
                $ministereData
            );

            // Créer un email admin unique par ministère, ex: admin.ministere-numerique@bj
            $adminEmail = 'admin.' . $ministere->slug . '@bj';

            $adminMinistere = User::where('email', $adminEmail)->first();

            if (!$adminMinistere) {
                $adminMinisterePersonne = Personne::firstOrCreate(
                    ['nom' => 'Admin', 'prenom' => ucfirst($ministere->slug)],
                    [
                        'poste' => 'Administrateur Ministère',
                        'organismeId' => $ministere->id
                    ]
                );

                $passwordMinistere = 'Ministere123!';

                $adminMinistere = User::create([
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

                $adminMinistere->roles()->attach([$roleOrganisation->id]);

                $this->command->info("✅ Admin créé pour le ministère {$ministere->nom}");
                $this->command->info("📧 Email : {$adminEmail}");
                $this->command->info("🔑 Mot de passe : {$passwordMinistere}");
            } else {
                $this->command->info("ℹ️ Le compte admin existe déjà pour le ministère {$ministere->nom}");
            }

            // --- Groupe Comité de validation Ministériel ---
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

            // 1. Créer le rôle spécifique au groupe ministériel
            $roleMembreComite = Role::firstOrCreate(
                [
                    'slug' => 'membre-comite-ministeriel',
                    'roleable_type' => get_class($ministere),
                    'roleable_id' => $ministere->id,
                ],
                [
                    'nom' => 'Membre Comité Ministériel',
                    'description' => 'Membre du Comité de validation Ministériel pour ' . $ministere->nom,
                    'roleable_type' => get_class($ministere),
                    'roleable_id' => $ministere->id,
                ]
            );

            $this->command->info("✅ Rôle Membre Comité Ministériel créé pour {$ministere->nom}");

            // 2. Lier ce rôle au groupe "Comité de validation Ministériel"
            // (Supposons que GroupeUtilisateur a une relation roles(), sinon il faut la créer)
            $groupeComiteValidation->roles()->syncWithoutDetaching([$roleMembreComite->id]);

            $this->command->info("✅ Rôle associé au groupe Comité de validation Ministériel pour {$ministere->nom}");

            // 3. Créer un ou plusieurs utilisateurs "membre comité" rattachés au ministère et au groupe

            $emailMembre = "membre-comite.{$ministere->slug}@ministere.bj";
            $membre = User::where('email', $emailMembre)->first();

            if (!$membre) {
                $membrePersonne = Personne::firstOrCreate(
                    ['nom' => 'Membre', 'prenom' => 'Comité'],
                    [
                        'poste' => 'Membre Comité Validation',
                        'organismeId' => $ministere->id
                    ]
                );

                $passwordMembre = 'MembreComite123!';

                $membre = User::create([
                    'provider' => 'local',
                    'provider_user_id' => $emailMembre,
                    'username' => $emailMembre,
                    'email' => $emailMembre,
                    'status' => 'actif',
                    'is_email_verified' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make($passwordMembre),
                    'personneId' => $membrePersonne->id,
                    'roleId' => null,
                    'last_connection' => now(),
                    'ip_address' => '127.0.0.1',
                    'type' => 'membre-comite',
                    'profilable_id' => $ministere->id,
                    'profilable_type' => get_class($ministere),
                    'account_verification_request_sent_at' => Carbon::now(),
                    'token' => str_replace(['/', '\\', '.'], '', Hash::make($ministere->id . Hash::make($emailMembre) . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                    'link_is_valide' => true,
                    'created_at' => now(),
                    'lastRequest' => now()
                ]);

                $groupeComiteValidation->users()->attach([$membre->id]);

                // Attacher le rôle (relation many-to-many)
                //$membre->roles()->attach([$roleMembreComite->id]);

                // Attacher le groupe (relation many-to-many ou autre)
                //$membre->groupes()->attach([$groupeComiteValidation->id]);

                $this->command->info("✅ Compte membre Comité Validation créé pour {$ministere->nom}");
                $this->command->info("📧 Email : {$emailMembre}");
                $this->command->info("🔑 Mot de passe : {$passwordMembre}");
            } else {
                $this->command->info("ℹ️ Le compte membre Comité Validation existe déjà pour {$ministere->nom}");
            }

            $this->command->info("✅ Groupe Comité de validation Ministériel créé pour {$ministere->nom}");
            $roleDpaf = Role::firstOrCreate(['slug' => 'dpaf'], ['nom' => 'DPAF']);

            if (!$roleDpaf) {
                $this->command->error('⚠️ Le rôle DPAF n\'existe pas.');
                return;
            }

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
                    ['nom' => 'Admin', 'prenom' => 'DPAF'],
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

            /**
             * Rôle Responsable projet du ministère
             */
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

            /**
             * Utilisateur Responsable projet
             */
            $emailResponsableProjet = "responsable-projet.{$slugMin}@ministere.bj";
            $responsableProjet = User::where('email', $emailResponsableProjet)->first();

            if (!$responsableProjet) {
                $responsableProjetPersonne = Personne::firstOrCreate(
                    ['nom' => 'Responsable', 'prenom' => 'Projet'],
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

            /**
             * Rôle Responsable hiérachique
             */
            $roleResponsableHierarchique = Role::firstOrCreate(
                [
                    'slug' => 'responsable-hierachique',
                    'roleable_type' => get_class($ministere),
                    'roleable_id' => $ministere->id,
                ],
                [
                    'nom' => 'Responsable hiérachique',
                    'slug' => 'responsable-hierachique',
                    'roleable_type' => get_class($ministere),
                    'roleable_id' => $ministere->id,
                    'description' => 'Responsable hiérachique du ' . $ministere->nom,
                ]
            );

            // Récupérer les IDs des permissions correspondantes aux slugs
            $permissionIds = Permission::whereIn('slug', $this->responsableHierarchiquePermissionsSlugs)->pluck('id')->toArray();

            // Synchroniser les permissions au rôle
            $roleResponsableHierarchique->permissions()->sync($permissionIds);

            $this->command->info("✅ Rôle Responsable hiérachique créé pour {$ministere->nom}");

            /**
             * Utilisateur Responsable hiérachique
             */
            $emailResponsableHier = "responsable-hierachique.{$slugMin}@ministere.bj";
            $responsableHierarchique = User::where('email', $emailResponsableHier)->first();

            if (!$responsableHierarchique) {
                $responsableHierarchiquePersonne = Personne::firstOrCreate(
                    ['nom' => 'Responsable', 'prenom' => 'Hiérarchique'],
                    [
                        'poste' => 'Responsable hiérachique',
                        'organismeId' => $ministere->id
                    ]
                );

                $passwordResponsableHier = 'ResponsableHier123!';

                $responsableHierarchique = User::create([
                    'provider' => 'local',
                    'provider_user_id' => $emailResponsableHier,
                    'username' => $emailResponsableHier,
                    'email' => $emailResponsableHier,
                    'status' => 'actif',
                    'is_email_verified' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make($passwordResponsableHier),
                    'personneId' => $responsableHierarchiquePersonne->id,
                    'roleId' => $roleResponsableHierarchique->id,
                    'last_connection' => now(),
                    'ip_address' => '127.0.0.1',
                    'type' => 'responsable-hierachique',
                    'profilable_id' => $ministere->id,
                    'profilable_type' => get_class($ministere),
                    'account_verification_request_sent_at' => Carbon::now(),
                    'token' => str_replace(['/', '\\', '.'], '', Hash::make($ministere->id . Hash::make($emailResponsableHier) . Hash::make(Hash::make(strtotime(Carbon::now()))))),
                    'link_is_valide' => true,
                    'created_at' => now(),
                    'lastRequest' => now()
                ]);

                $responsableHierarchique->roles()->attach([$roleResponsableHierarchique->id]);

                $this->command->info("✅ Compte Responsable hiérachique créé avec succès pour {$ministere->nom}");
                $this->command->info("📧 Email: {$emailResponsableHier}");
                $this->command->info("🔑 Mot de passe: {$passwordResponsableHier}");
            } else {
                $this->command->info("ℹ️ Le compte Responsable hiérachique existe déjà pour {$ministere->nom}");
            }

            $this->command->info("✅ Espaces de travail créés avec succès pour {$ministere->nom} !");


            foreach ($enfants as $enfantData) {
                $enfantData['parentId'] = $ministere->id;
                Organisation::updateOrCreate(
                    ['slug' => $enfantData['slug']],
                    $enfantData
                );
            }
        }
    }
}
