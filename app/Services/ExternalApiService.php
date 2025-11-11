<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isEmpty;

class ExternalApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retryDelay;

    public function __construct()
    {
        $this->baseUrl = config('services.external_api.base_url');
        $this->apiKey = config('services.external_api.api_key');
        $this->timeout = config('services.external_api.timeout', 30);
        $this->retryTimes = config('services.external_api.retry_times', 3);
        $this->retryDelay = config('services.external_api.retry_delay', 1000); // en millisecondes
    }

    /**
     * Envoyer les données de validation d'un projet au système externe
     *
     * @param array $data Les données à envoyer
     * @return array|null Résultat de l'appel API ou null en cas d'échec
     */
    public function envoyerValidationProjet(array $data): ?array
    {
        try {
            // Log de la tentative d'envoi
            Log::info('Envoi des données de validation au système externe', [
                'projet_id' => $data['projet']['id'] ?? null,
                'action' => $data['action'] ?? null
            ]);

            // Effectuer l'appel HTTP avec retry automatique
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retryDelay, function ($exception, $request) {
                    // Retry seulement sur les erreurs de connexion ou timeout
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException ||
                        $exception instanceof \Illuminate\Http\Client\RequestException;
                })
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/projets/validation', $data);

            // Vérifier le succès de la requête
            if ($response->successful()) {
                Log::info('Données de validation envoyées avec succès au système externe', [
                    'projet_id' => $data['projet']['id'] ?? null,
                    'response_status' => $response->status()
                ]);

                return $response->json();
            }

            // Log de l'échec avec le code HTTP
            Log::warning('Échec de l\'envoi des données au système externe', [
                'projet_id' => $data['projet']['id'] ?? null,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            // Log de l'erreur sans bloquer le processus
            Log::error('Erreur lors de l\'envoi des données au système externe', [
                'projet_id' => $data['projet']['id'] ?? null,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Préparer les données du projet pour l'envoi au système externe
     *
     * @param \App\Models\Projet $projet
     * @param \App\Models\Rapport $rapport
     * @param \App\Models\Evaluation $evaluation
     * @param string $action
     * @param array $data Données supplémentaires
     * @return array
     */
    public function preparerDonneesValidation($projet, $rapport, $evaluation, string $action, array $data): array
    {
        return [
            'action' => $action,
            'date_validation' => now()->toIso8601String(),
            'validateur' => [
                'id' => auth()->id(),
                'nom' => auth()->user()->name ?? null,
                'email' => auth()->user()->email ?? null,
            ],
            'projet' => [
                'id' => $projet->id,
                'titre' => $projet->titre,
                'description' => $projet->description,
                'statut' => $projet->statut->value,
                'phase' => $projet->phase,
                'sous_phase' => $projet->sous_phase,
                'date_debut_etude' => $projet->date_debut_etude?->toIso8601String(),
                'date_fin_etude' => $projet->date_fin_etude?->toIso8601String(),
                'montant' => $projet->montant,
                'devise' => $projet->devise,
                'programme_id' => $projet->programme_id,
            ],
            'rapport' => [
                'id' => $rapport->id,
                'type' => $rapport->type,
                'intitule' => $rapport->intitule,
                'statut' => $rapport->statut,
                'decision' => $rapport->decision,
                'recommandation' => $rapport->recommandation,
                'date_soumission' => $rapport->date_soumission?->toIso8601String(),
                'date_validation' => $rapport->date_validation?->toIso8601String(),
                'commentaire_validation' => $rapport->commentaire_validation,
            ],
            'evaluation' => [
                'id' => $evaluation->id,
                'type_evaluation' => $evaluation->type_evaluation,
                'resultats_evaluation' => $evaluation->resultats_evaluation,
                'commentaire' => $evaluation->commentaire,
                'date_debut_evaluation' => $evaluation->date_debut_evaluation?->toIso8601String(),
                'date_fin_evaluation' => $evaluation->date_fin_evaluation?->toIso8601String(),
            ],
            'commentaire' => $data['commentaire'] ?? null,
        ];
    }

    /**
     * Envoyer un projet mature au système SIGFP
     *
     * @param array $payload Les données formatées pour SIGFP
     * @return array|null Résultat de l'appel API ou null en cas d'échec
     */
    public function envoyerProjetMature(array $payload): ?array
    {
        try {
            $url = 'https://sigfp.reforme.topsystem-group.com/selection-projet/xrod/sync/projetmature';

            // Encoder le payload en JSON pour vérification
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            Log::info('Envoi du projet mature au système SIGFP', [
                'url' => $url,
                'transaction_id' => $payload['transactionId'] ?? null,
                'payload_size' => strlen($jsonPayload) . ' bytes'
            ]);

            // Effectuer l'appel HTTP sans retry (le retry est géré par le Job)
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Accept' => 'application/json',
                ])
                ->withBody($jsonPayload, 'application/json')
                ->post($url);

            // Vérifier le succès de la requête
            if ($response->successful()) {
                Log::info('Projet mature envoyé avec succès au système SIGFP', [
                    'transaction_id' => $payload['transactionId'] ?? null,
                    'response_status' => $response->status()
                ]);

                return $response->json();
            }

            // Log de l'échec avec le code HTTP
            Log::warning('Échec de l\'envoi du projet mature au système SIGFP', [
                'transaction_id' => $payload['transactionId'] ?? null,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            // Log de l'erreur
            Log::error('Erreur lors de l\'envoi du projet mature au système SIGFP', [
                'transaction_id' => $payload['transactionId'] ?? null,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            throw $e; // Relancer l'exception pour que le Job puisse la gérer
        }
    }

    /**
     * Préparer le payload au format SIGFP pour un projet mature
     *
     * @param \App\Models\Projet $projet
     * @param array $additionalData Données supplémentaires
     * @return array
     */
    public function preparerPayloadProjetMature($projet, array $additionalData = []): array
    {
        // Générer un ID de transaction unique
        $transactionId = 'TX-' . now()->format('Ymd') . '-' . str_pad($projet->id, 6, '0', STR_PAD_LEFT);

        return [
            'transactionId' => $transactionId,
            'transactionType' => 'SYNC_DATA',
            'systemeSource' => 'BIP',
            'systemeDestination' => 'SIGFP_SELECTION',
            'versionSchema' => '1.0.0',
            'data' => [
                $this->construireProjetData($projet, $additionalData)
            ]
        ];
    }

    /**
     * Construire les données complètes d'un projet au format SIGFP
     *
     * @param \App\Models\Projet $projet
     * @param array $additionalData
     * @return array
     */
    protected function construireProjetData($projet, array $additionalData): array
    {
        return [
            'origineProjet' => $this->construireOrigineProjet($projet),
            'descriptionSommaire' => $this->construireDescriptionSommaire($projet),
            'partiesPrenantes' => $this->construirePartiesPrenantes($projet),
            'dateDebutEtude' => $this->formatDate($projet->date_debut_etude),
            'dateFinEtude' => $this->formatDate($projet->date_fin_etude),
            'datePrevueDemarrage' => $this->formatDate($projet->date_prevue_demarrage),
            'dureeExecutionPrev' => (int) ($projet->duree ?? 730),
            'porteurDuProjet' => $projet->porteur_projet ?? 'Non défini',
            'demandeur' => $projet->demandeur?->personne->nom . ' ' . $projet->demandeur?->personne->prenom ?? 'Non défini',
            'responsableProjet' => $projet->responsable?->personne->nom . ' ' . $projet->responsable?->personne->prenom ?? 'Non défini',
            'identifiantBip' => $projet->identifiant_bip ?? 'BIP-' . ($projet->hashed_id ?? $projet->id),
            'coutEstimatif' => (float) ($projet->cout_estimatif_projet['montant'] ?? 0),
            'sourceFinancement' => $this->construireSourcesFinancement($projet),
            'categorieProjet' => $this->construireCategorieProjet($projet),
            'zonesIntervention' => $this->construireZonesIntervention($projet),
            'cadreStrategique' => $this->construireCadreStrategique($projet),
            'informationSectorielle' => $this->construireInformationSectorielle($projet),
            'lienFicheProjet' => config('app.client_app_url') . '/projets/' . ($projet->hashed_id ?? $projet->id),
            'evaluations' => $this->construireEvaluations($projet),
        ];
    }

    /**
     * Formater une date pour l'API (gère Carbon, string ou null)
     */
    protected function formatDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        if (is_string($date)) {
            try {
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }

        return null;
    }

    /**
     * Construire les données d'origine du projet
     */
    protected function construireOrigineProjet($projet): array
    {
        return [
            'titre' => $projet->titre_projet ?? $projet->titre,
            'sigle' => $projet->sigle ?? '',
            'origine' => $projet->origine ?? '',
            'fondement' => $this->construireFondement($projet),
            'situationActuelle' => $projet->situation_actuelle ?? '',
            'situationDesiree' => $projet->situation_desiree ?? '',
            'contraintes' => $projet->contraintes ?? '',
        ];
    }

    /**
     * Construire le fondement du projet (ODD, PND, PAG)
     */
    protected function construireFondement($projet): array
    {
        // Récupérer les ODD du projet
        $odds = $projet->odds->map(function ($odd) {
            return $odd->odd ?? $odd->nom;
        })->filter()->values()->toArray();

        // Récupérer les cibles
        $cibles = $projet->cibles->map(function ($cible) {
            return $cible->cible ?? $cible->nom;
        })->filter()->values()->toArray();

        // Récupérer les orientations_strategique_png
        $orientations_strategique_png = $projet->orientations_strategique_png->map(function ($orientation_strategique_png) {
            return $orientation_strategique_png->intitule;
        })->filter()->values()->toArray();

        // Récupérer les objectifs_strategique_png
        $objectifs_strategique_png = $projet->objectifs_strategique_png->map(function ($objectif_strategique_png) {
            return $objectif_strategique_png->intitule;
        })->filter()->values()->toArray();

        // Récupérer les resultats_strategique_png
        $resultats_strategique_png = $projet->resultats_strategique_png->map(function ($resultat_strategique_png) {
            return $resultat_strategique_png->intitule;
        })->filter()->values()->toArray();

        // Récupérer les piliers_pag
        $piliers_pag = $projet->piliers_pag->map(function ($pilier_pag) {
            return $pilier_pag->intitule;
        })->filter()->values()->toArray();

        // Récupérer les axes_pag
        $axes_pag = $projet->axes_pag->map(function ($axe_pag) {
            return $axe_pag->intitule;
        })->filter()->values()->toArray();

        // Récupérer les axes_pag
        $actions_pag = $projet->actions_pag->map(function ($action_pag) {
            return $action_pag->intitule;
        })->filter()->values()->toArray();

        return [
            'odds' => !empty($odds) ? $odds : [],
            'cibles' => !empty($cibles) ? $cibles : [],
            'informationsPND' => [
                'orientationsStrategiques' => !empty($orientations_strategique_png) ? $orientations_strategique_png : [],
                'objectifsStrategiques' => !empty($objectifs_strategique_png) ? $objectifs_strategique_png : [],
                //'resultatsStrategiques' => !empty($resultats_strategique_png) ? $resultats_strategique_png : [],
                //$projet->orientations_strategique_png ?? [],
                //'objectifsStrategiques' => $projet->objectifs_strategique_png ?? [],
            ],
            'informationsPAG' => [
                /*'piliersStrategiques' => $projet->piliers_pag ?? [],
                'axes' => $projet->axes_pag ?? [],
                'actions' => $projet->actions_pag ?? [],*/


                'piliersStrategiques' => !empty($piliers_pag) ? $piliers_pag : [],
                'axes' => !empty($axes_pag) ? $axes_pag : [],
                'actions' => !empty($actions_pag) ? $actions_pag : [],
            ],
        ];
    }

    /**
     * Construire la description sommaire du projet
     */
    protected function construireDescriptionSommaire($projet): array
    {
        // Parser description_extrants si JSON
        $descriptionExtrants = [];
        if (is_string($projet->description_extrants)) {
            try {
                $descriptionExtrants = json_decode($projet->description_extrants, true) ?? [];
            } catch (\Exception $e) {
                $descriptionExtrants = [];
            }
        } else if (is_array($projet->description_extrants)) {
            $descriptionExtrants = $projet->description_extrants;
        }

        // Parser echeancier si JSON
        $echeancier = [];
        if (is_string($projet->echeancier)) {
            try {
                $echeancier = $projet->echeancier;
            } catch (\Exception $e) {
                $echeancier = "";
            }
        } else if (is_array($projet->echeancier)) {
            $echeancier = $projet->echeancier;
        }

        return [
            'objectifGeneral' => $projet->ideeProjet->objectif_general ?? '',
            'objectifsSpecifiques' => $projet->objectifs_specifiques ?? [],
            'resultatsAttendus' => $projet->resultats_attendus ?? [],
            'echeancierPrincipauxExtrants' => [["indicateurRealisation" => $echeancier]],
            'descriptionPrincipauxExtrants' => !isEmpty($descriptionExtrants) ? [["description" => $descriptionExtrants[0]]] : [],
            'descriptionsExtrants' => $descriptionExtrants,
            'descriptionSommaire' => $projet->description ?? '',
            'caracteristiquesTechniques' => is_array($projet->caracteristiques) ? $projet->caracteristiques : [],
            'coutsEtBenefices' => [
                'coutEstimeXOF' => (float) ($projet->cout_estimatif_projet["montant"] ?? 0),
                'coutEstimeEUR' => (float) ($projet->cout_euro ?? 0),
                'coutEstimeUS' => (float) ($projet->cout_dollar_americain ?? 0),
                'beneficesEstimes' => 0,
                'notes' => '',
            ],
            'aspectsOrganisationnels' => $projet->aspect_organisationnel ?? '',
            'risquesImmediats' => is_array($projet->risques_immediats) ? $projet->risques_immediats : [],
            'conclusionsRecommandations' => $projet->conclusions ?? '',
            'autresSolutionsAlternatives' => [],
        ];
    }

    /**
     * Construire les parties prenantes
     */
    protected function construirePartiesPrenantes($projet): array
    {
        return [
            'partiesPrenantes' => is_array($projet->parties_prenantes) ? $projet->parties_prenantes : [],
            'publicCible' => is_array($projet->ideeProjet->public_cible) ? $projet->ideeProjet->public_cible : [],
            'cibles' => $projet->cibles->map(function ($cible) {
                return $cible->cible ?? $cible->nom;
            })->filter()->values()->toArray(),
        ];
    }

    /**
     * Construire la catégorie du projet
     */
    protected function construireCategorieProjet($projet): array
    {
        return [
            'nom' => $projet->categorie?->categorie ?? 'Non catégorisé',
            'code' => $projet->categorie?->slug ?? 'NC',
        ];
    }

    /**
     * Construire les zones d'intervention
     */
    protected function construireZonesIntervention($projet): array
    {
        $lieux = $projet->lieuxIntervention()->with(['departement', 'commune', 'arrondissement', 'village'])->get();

        return $lieux->map(function ($lieu) {
            // Construire le code en prenant le niveau le plus précis disponible
            $code = $lieu->village?->code
                ?? $lieu->arrondissement?->code
                ?? $lieu->commune?->code
                ?? $lieu->departement?->code
                ?? 'ZN-' . $lieu->id;

            return [
                'code' => $code,
                /*'departement' => $lieu->departement?->nom ?? '',
                'commune' => $lieu->commune?->nom ?? '',
                'arrondissement' => $lieu->arrondissement?->nom ?? '',
                'village' => $lieu->village?->nom ?? '',*/
                'longitude' => $lieu->longitude ?? 0.0,
                'latitude' => $lieu->latitude ?? 0.0,
            ];
        })->toArray();
    }

    /**
     * Construire les sources de financement
     */
    protected function construireSourcesFinancement($projet): string
    {
        $sources = $projet->sources_de_financement()->get();

        if ($sources->isEmpty()) {
            return 'Non défini';
        }

        return $sources->map(function ($source) {
            return $source->nom ?? $source->libelle;
        })->join(', ');
    }

    /**
     * Construire le cadre stratégique
     */
    protected function construireCadreStrategique($projet): array
    {
        $odds = $projet->odds()->get()->map(function ($odd) {
            return $odd->odd ?? $odd->nom;
        })->filter()->values()->toArray();

        $cibles = $projet->cibles()->get()->map(function ($cible) {
            return $cible->cible ?? $cible->nom;
        })->filter()->values()->toArray();

        return [
            'odds' => $odds,
            'cibles' => $cibles,
        ];
    }

    /**
     * Construire les informations sectorielles
     */
    protected function construireInformationSectorielle($projet): array
    {
        return [
            'code' => $projet->secteur?->code ?? 'NC',
            'secteur' => $projet->secteur->parent?->nom ?? 'Non défini',
            'sousSecteur' => $projet->secteur?->nom ?? 'Non défini',
        ];
    }

    /**
     * Construire les évaluations complètes
     */
    protected function construireEvaluations($projet): array
    {
        return [
            //'pertinence' => $this->construireEvaluationPertinence($projet),
            'climatique' => $this->construireEvaluationClimatique($projet),
            'amc' => $this->construireEvaluationAmc($projet),
            'noteConceptuelle' => $this->construireNoteConceptuelle($projet),
            'etudePrefaisabilite' => $this->construireEtudePrefaisabilite($projet),
            'etudeFaisabilite' => $this->construireEtudeFaisabilite($projet),
            'evaluationExAnte' => $this->construireEvaluationExAnte($projet),

        ];
    }

    /**
     * Construire l'évaluation pertinence
     */
    protected function construireEvaluationPertinence($projet): array
    {
        // Utiliser la relation comme dans ProjetResource
        $evalPertinence = $projet->evaluationpertinence->first();

        return [
            'scorePertinence' => (float) ($projet->score_pertinence ?? 0),
            'criteres' => $evalPertinence?->evaluation ?? [],
            'fichiersJoints' => $this->construireFichiersJoints($evalPertinence),
        ];
    }

    /**
     * Construire l'évaluation climatique
     */
    protected function construireEvaluationClimatique($projet): array
    {
        // L'évaluation climatique est contenue dans l'évaluation AMC
        $evalAmc = $projet->evaluationAMC->first();

        return [
            'scoreClimatique' => (float) ($projet->score_climatique ?? 0),
            'criteres' => collect(data_get($evalAmc, 'evaluation.climatique.evaluation_effectuer', []))->map(function ($critere) {
                //dd($critere);
                return [
                    "nomCritere" => $critere["critere"]["intitule"],//"Capacité institutionnelle",
                    //"nomCritere" => "Capacité institutionnelle",
                    "notation" =>$critere["note"],
                    "ponderation" =>$critere["critere"]["ponderation"]
                ];
            })->toArray(),

            /*'criteres' => collect(data_get($evalAmc, 'evaluation.climatique', []))->map(function ($critere) {
                return [
                    "nomCritere" => $critere["critere"]["intitule"],//"Capacité institutionnelle",
                    "notation" => $critere["note"],//$critere["note"],
                    "ponderation" => $critere["critere"]["ponderation"]
                ];
            })->toArray(),*/
            'fichiersJoints' => $this->construireFichiersJoints($evalAmc),
        ];
    }

    /**
     * Construire l'évaluation AMC
     */
    protected function construireEvaluationAmc($projet): array
    {
        // Utiliser la relation comme dans ProjetResource
        $evalAmc = $projet->evaluationAMC->first();

        return [
            'scoreAmc' => (float) ($projet->score_amc ?? 0),
            'criteres' => collect(data_get($evalAmc, 'evaluation.amc', []))->map(function ($critere) {
                return [
                    "nomCritere" => $critere["critere"]["intitule"],//"Capacité institutionnelle",
                    "notation" => $critere["note"],//$critere["note"],
                    "ponderation" => $critere["critere"]["ponderation"]
                ];
            })->toArray(),
            'fichiersJoints' => $this->construireFichiersJoints($evalAmc),
        ];
    }

    /**
     * Construire la note conceptuelle
     */
    protected function construireNoteConceptuelle($projet): array
    {
        $noteConceptuelle = $projet->noteConceptuelle;

        // Récupérer les fichiers de la note conceptuelle
        $fichiersJoints = [];
        if ($noteConceptuelle && $noteConceptuelle->fichiers) {
            $fichiersJoints = $noteConceptuelle->fichiers->map(function ($fichier) {
                return [
                    'nomFichier' => $fichier->nom_stockage ?? 'fichier',
                    'lienFichier' => $this->genererLienFichier($fichier),
                ];
            })->toArray();
        }

        // Récupérer ou créer une évaluation pour tracer la validation
        // Chercher d'abord une évaluation EN COURS (statut != 1) pour éviter d'écraser l'historique
        $evaluation = $projet->evaluations()
            ->where('type_evaluation', 'validation-etude-profil')
            ->where('statut', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($evaluation) {
            $decisionEtudeProfil = $evaluation->evaluation['decision'];
        } else {
            $decisionEtudeProfil = 'N/A';
        }

        return [
            'resume' => "", //json_encode($noteConceptuelle?->note_conceptuelle) ?? '',
            'fichiersJoints' => $fichiersJoints,
            'lienNoteConceptuelle' => null,
            'lienAppreciationNote' => null,
            'decisionEtudeProfil' => $decisionEtudeProfil, //$noteConceptuelle?->decision ?? 'EN_COURS',
        ];
    }

    /**
     * Construire l'étude de préfaisabilité
     */
    protected function construireEtudePrefaisabilite($projet): array
    {
        // Utiliser les relations comme dans ProjetResource
        $tdrPrefaisabilite = $projet->tdrPrefaisabilite->first();
        $rapportPrefaisabilite = $projet->rapportPrefaisabilite->first();

        // Récupérer le premier fichier du TDR
        $premierFichierTdr = null;
        if ($tdrPrefaisabilite && $tdrPrefaisabilite->fichiers && $tdrPrefaisabilite->fichiers->isNotEmpty()) {
            //$premierFichierTdr = $tdrPrefaisabilite->fichiers->first();

            // Fichiers par type
            $typeDocument = 'tdr-prefaisabilite';
            $premierFichierTdr = $tdrPrefaisabilite->fichiers->where('metadata.type_document', $typeDocument)->first();
            //return new FichierResource($fichier);
        }

        // Récupérer le premier fichier du rapport
        $premierFichierRapport = null;
        $premierFichierProcesVerbal = null;
        $premierFichierListePresence = null;
        if ($rapportPrefaisabilite) {
            // Essayer d'abord avec fichiersRapport, puis fichiers
            if ($rapportPrefaisabilite->fichiersRapport && $rapportPrefaisabilite->fichiersRapport->isNotEmpty()) {
                $premierFichierRapport = $rapportPrefaisabilite->fichiersRapport->first();
            } elseif ($rapportPrefaisabilite->fichiers->where('categorie', 'rapport') && $rapportPrefaisabilite->fichiers->where('categorie', 'rapport')->isNotEmpty()) {
                $premierFichierRapport = $rapportPrefaisabilite->fichiers->where('categorie', 'rapport')->first();
            }

            // Essayer d'abord avec fichiersRapport, puis fichiers
            if ($rapportPrefaisabilite->procesVerbaux && $rapportPrefaisabilite->procesVerbaux->isNotEmpty()) {
                $premierFichierProcesVerbal = $rapportPrefaisabilite->procesVerbaux->first();
            } elseif ($rapportPrefaisabilite->fichiers->where('categorie', 'proces-verbal') && $rapportPrefaisabilite->fichiers->where('categorie', 'proces-verbal')->isNotEmpty()) {
                $premierFichierProcesVerbal = $rapportPrefaisabilite->fichiers->where('categorie', 'proces-verbal')->first();
            }

            // Essayer d'abord avec fichiersRapport, puis fichiers
            if ($rapportPrefaisabilite->fichiers->where('categorie', 'liste-presence') && $rapportPrefaisabilite->fichiers->where('categorie', 'liste-presence')->isNotEmpty()) {
                $premierFichierListePresence = $rapportPrefaisabilite->fichiers->where('categorie', 'liste-presence')->first();
            }
        }

        return [
            'dateDebut' => $this->formatDate($tdrPrefaisabilite?->date_debut),
            'dateFin' => $this->formatDate($tdrPrefaisabilite?->date_fin),
            'informationCabinet' => [
                'nom' => $rapportPrefaisabilite?->info_cabinet_etude['nom_cabinet'] ?? 'Non défini',
                'pays' => $rapportPrefaisabilite?->info_cabinet_etude?->pays ?? 'Bénin',
                'info_details_cabinet' => $rapportPrefaisabilite?->info_cabinet_etude,
            ],
            'informationFinancement' => [
                'source' => $projet?->info_etude_prefaisabilite['est_finance'] ? 'Fond de financement de preparation' : 'Non financee',
                'montant' => (float) ($projet?->info_etude_prefaisabilite['montant'] ?? 0),
                'info_details_etude' => $projet?->info_etude_prefaisabilite,
            ],
            'lienTdr' => $premierFichierTdr ? $this->genererLienFichier($premierFichierTdr) : null,
            'lienExcelAppreciation' => null,
            'lienRapportTeleverse' => $premierFichierRapport ? $this->genererLienFichier($premierFichierRapport) : null,
            'lienProcesVerbal' => $premierFichierProcesVerbal ? $this->genererLienFichier($premierFichierProcesVerbal) : null,
            'lienListePresence' => $premierFichierListePresence ? $this->genererLienFichier($premierFichierListePresence) : null,
            'fichiersJoints' => $this->construireFichiersJoints($rapportPrefaisabilite),
            'syntheseRecommandation' => $rapportPrefaisabilite?->recommandation ?? '',
            'decisionEtude' => $rapportPrefaisabilite?->decision ?? 'EN_COURS',
            'commentaireDecision' => $rapportPrefaisabilite?->commentaire ?? '',
        ];
    }

    /**
     * Construire l'étude de faisabilité
     */
    protected function construireEtudeFaisabilite($projet): array
    {
        // Utiliser les relations comme dans ProjetResource
        $tdrFaisabilite = $projet->tdrFaisabilite->first();
        $rapportFaisabilite = $projet->rapportFaisabilite->first();

        // Récupérer le premier fichier du TDR
        $premierFichierTdr = null;
        if ($tdrFaisabilite && $tdrFaisabilite->fichiers && $tdrFaisabilite->fichiers->isNotEmpty()) {
            // Fichiers par type
            $typeDocument = 'tdr-faisabilite';
            $premierFichierTdr = $tdrFaisabilite->fichiers->where('metadata.type_document', $typeDocument)->first();
        }

        // Récupérer le premier fichier du rapport
        $premierFichierRapport = null;
        $premierFichierProcesVerbal = null;
        $premierFichierListePresence = null;
        if ($rapportFaisabilite) {
            // Essayer d'abord avec fichiersRapport, puis fichiers
            if ($rapportFaisabilite->fichiersRapport && $rapportFaisabilite->fichiersRapport->isNotEmpty()) {
                $premierFichierRapport = $rapportFaisabilite->fichiersRapport->first();
            } elseif ($rapportFaisabilite->fichiers->where('categorie', 'rapport') && $rapportFaisabilite->fichiers->where('categorie', 'rapport')->isNotEmpty()) {
                $premierFichierRapport = $rapportFaisabilite->fichiers->where('categorie', 'rapport')->first();
            }

            // Essayer d'abord avec procesVerbaux, puis fichiers
            if ($rapportFaisabilite->procesVerbaux && $rapportFaisabilite->procesVerbaux->isNotEmpty()) {
                $premierFichierProcesVerbal = $rapportFaisabilite->procesVerbaux->first();
            } elseif ($rapportFaisabilite->fichiers->where('categorie', 'proces-verbal') && $rapportFaisabilite->fichiers->where('categorie', 'proces-verbal')->isNotEmpty()) {
                $premierFichierProcesVerbal = $rapportFaisabilite->fichiers->where('categorie', 'proces-verbal')->first();
            }

            // Récupérer liste de présence
            if ($rapportFaisabilite->fichiers->where('categorie', 'liste-presence') && $rapportFaisabilite->fichiers->where('categorie', 'liste-presence')->isNotEmpty()) {
                $premierFichierListePresence = $rapportFaisabilite->fichiers->where('categorie', 'liste-presence')->first();
            }
        }

        return [
            'dateDebut' => $this->formatDate($tdrFaisabilite?->date_debut),
            'dateFin' => $this->formatDate($tdrFaisabilite?->date_fin),
            'informationCabinet' => [
                'nom' => $rapportFaisabilite?->info_cabinet_etude['nom_cabinet'] ?? 'Non défini',
                'pays' => $rapportFaisabilite?->info_cabinet_etude?->pays ?? 'Bénin',
                'info_details_cabinet' => $rapportFaisabilite?->info_cabinet_etude,
            ],
            'informationFinancement' => [
                'source' => $projet?->info_etude_faisabilite['est_finance'] ? 'Fond de financement de preparation' : 'Non financee',
                'montant' => (float) ($projet?->info_etude_faisabilite['montant'] ?? 0),
                'info_details_etude' => $projet?->info_etude_faisabilite,
            ],
            'lienTdr' => $premierFichierTdr ? $this->genererLienFichier($premierFichierTdr) : null,
            'lienExcelAppreciation' => null,
            'lienRapportTeleverse' => $premierFichierRapport ? $this->genererLienFichier($premierFichierRapport) : null,
            'lienProcesVerbal' => $premierFichierProcesVerbal ? $this->genererLienFichier($premierFichierProcesVerbal) : null,
            'lienListePresence' => $premierFichierListePresence ? $this->genererLienFichier($premierFichierListePresence) : null,
            'fichiersJoints' => $this->construireFichiersJoints($rapportFaisabilite),
            'syntheseRecommandation' => $rapportFaisabilite?->recommandation ?? '',
            'decisionEtude' => $rapportFaisabilite?->decision ?? 'EN_COURS',
            'commentaireDecision' => $premierFichierRapport?->commentaire ?? '',
        ];
    }

    /**
     * Construire l'évaluation ex-ante
     */
    protected function construireEvaluationExAnte($projet): array
    {
        // Utiliser la relation comme dans ProjetResource
        $rapport = $projet->rapportEvaluationExAnte->first();

        return [
            'fichiersJoints' => $rapport ? $this->construireFichiersJoints($rapport) : [],
            'decisionEvaluation' => $rapport?->decision ?? 'EN_COURS',
        ];
    }

    /**
     * Construire la liste des fichiers joints
     */
    protected function construireFichiersJoints($entite): array
    {
        if (!$entite) {
            return [];
        }

        // Récupérer les fichiers selon le type d'entité
        $fichiers = null;

        if (method_exists($entite, 'fichiers') && $entite->fichiers) {
            $fichiers = $entite->fichiers;
        } elseif (method_exists($entite, 'fichiersRapport') && $entite->fichiersRapport) {
            $fichiers = $entite->fichiersRapport;
        } elseif (isset($entite->fichiers)) {
            $fichiers = $entite->fichiers;
        } elseif (isset($entite->fichiersRapport)) {
            $fichiers = $entite->fichiersRapport;
        }

        if (!$fichiers || $fichiers->isEmpty()) {
            return [];
        }

        return $fichiers->map(function ($fichier) {
            return [
                'nomFichier' => $fichier->nom_stockage ?? 'fichier',
                'lienFichier' => $this->genererLienFichier($fichier),
            ];
        })->toArray();
    }

    /**
     * Générer un lien de téléchargement pour un fichier
     * Utilise la même logique que FichierResource
     */
    protected function genererLienFichier($fichier): ?string
    {
        if (!$fichier) {
            return null;
        }

        // Générer l'URL de visualisation comme dans FichierResource
        // URLs sécurisées via hash MD5
        if ($fichier->hash_md5) {
            return route('api.fichiers.view', ['hash' => $fichier->hash_md5]);
        }

        return null;
    }

    /**
     * Tester la connexion au système externe
     *
     * @return bool
     */
    public function testerConnexion(): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/health');

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Échec du test de connexion au système externe', [
                'error_message' => $e->getMessage()
            ]);
            return false;
        }
    }
}
