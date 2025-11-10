<?php

namespace App\Http\Resources\projets;

use App\Http\Resources\BaseApiResource;
use App\Http\Resources\CibleResource;
use App\Http\Resources\FichierResource;
use App\Http\Resources\FinancementResource;
use App\Http\Resources\idees_projet\IdeeProjetResource;
use App\Http\Resources\LieuInterventionResource;
use App\Http\Resources\NoteConceptuelleResource;
use App\Http\Resources\OddResource;
use App\Http\Resources\RapportResource;
use App\Http\Resources\TdrResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProjetResource extends BaseApiResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hashed_id,
            // Identifiants et métadonnées
            'identifiant_bip' => $this->identifiant_bip,
            'identifiant_sigfp' => $this->identifiant_sigfp,
            'sigle' => $this->sigle,
            'titre_projet' => $this->titre_projet,

            'est_a_haut_risque' => $this->est_a_haut_risque,
            'est_dur' => $this->est_dur,
            "est_mou" => $this->est_mou,
            'est_ancien' => $this->est_ancien,
            'info_etude_prefaisabilite' => $this->info_etude_prefaisabilite,
            'info_etude_faisabilite' => $this->info_etude_faisabilite,

            // Statuts et phases
            'statut' => $this->statut?->value ?? $this->statut,
            'phase' => $this->phase?->value ?? $this->phase,
            'sous_phase' => $this->sous_phase?->value ?? $this->sous_phase,
            'type_projet' => $this->type_projet?->value ?? $this->type_projet,

            // Descriptions et contenus principaux
            'description' => $this->description,
            'description_projet' => $this->description_projet,
            'objectif_general' => $this->objectif_general,
            'situation_actuelle' => $this->situation_actuelle,
            'situation_desiree' => $this->situation_desiree,
            'contraintes' => $this->contraintes,
            'origine' => $this->origine,
            'fondement' => $this->fondement,

            // Détails techniques et organisationnels
            'caracteristiques' => $this->caracteristiques,
            'aspect_organisationnel' => $this->aspect_organisationnel,
            'description_extrants' => $this->description_extrants,
            'impact_environnement' => $this->impact_environnement,
            'echeancier' => $this->echeancier,
            'duree' => $this->duree,

            // Risques et conclusions
            'risques_immediats' => $this->risques_immediats,
            'constats_majeurs' => $this->constats_majeurs,
            'conclusions' => $this->conclusions,
            'sommaire' => $this->sommaire,
            'public_cible' => $this->public_cible,

            // Estimation des coûts
            'estimation_couts' => $this->estimation_couts,
            'cout_dollar_americain' => $this->cout_dollar_americain,
            'cout_dollar_canadien' => $this->cout_dollar_canadien,
            'cout_euro' => $this->cout_euro,

            // Scores d'évaluation
            'score_climatique' => $this->score_climatique,
            'score_amc' => $this->score_amc,
            'score_pertinence' => $this->score_pertinence,

            // Dates importantes
            'date_debut_etude' => $this->date_debut_etude,
            'date_fin_etude' => $this->date_fin_etude,
            'date_prevue_demarrage' => $this->date_prevue_demarrage,
            'date_effective_demarrage' => $this->date_effective_demarrage,

            // Données JSON structurées
            'decision' => $this->decision ?? [],
            'cout_estimatif_projet' => $this->cout_estimatif_projet ?? [],
            'ficheIdee' =>  $this->ficheIdee ?? [],
            'canevas_amc' => $this->canevas_amc ?? [],
            'canevas_climatique' => $this->canevas_climatique ?? [],
            'canevas_redaction_projet' => $this->ficheIdee ? (isset($this->ficheIdee["form"]) ? (array) $this->ficheIdee["form"] : []) : [],

            'parties_prenantes' => $this->parties_prenantes ?? [],
            'objectifs_specifiques' => $this->objectifs_specifiques ?? [],
            'resultats_attendus' => $this->resultats_attendus ?? [],
            'body_projet' => $this->body_projet ?? [],
            'description_decision' => $this->description_decision,

            'champs' => $this->champs->map(function ($champ) {
                return [
                    'id' => $champ->hashed_id,
                    'attribut' => $champ->attribut,
                    'value' => $champ->pivot->valeur,
                    'pivot_id' => $champ->pivot->id
                ];
            }),
            // Relations principales (loaded when needed)
            'secteur' => $this->whenLoaded('secteur'),
            'ministere' => $this->whenLoaded('ministere'),
            'categorie' => $this->whenLoaded('categorie'),
            'responsable' => $this->whenLoaded('responsable'),
            'demandeur' => $this->whenLoaded('demandeur'),
            'porteur_projet' => $this->porteur_projet,
            'ideeProjet' => new IdeeProjetResource($this->ideeProjet),
            'noteConceptuelle' => new NoteConceptuelleResource($this->noteConceptuelle),
            'fichiers_note_conceptuelle' => $this->noteConceptuelle?->fichiers
                ? FichierResource::collection($this->noteConceptuelle->fichiers->sortBy('ordre'))
                : [],
            'duree_vie' => $this->duree_vie,
            'investissement_initial' => $this->investissement_initial,
            'tri' => $this->tri,
            'van' => $this->van,
            'flux_tresorerie' => $this->flux_tresorerie,
            'taux_actualisation' => $this->taux_actualisation,

            // TDRs
            'tdr_prefaisabilite' => $this->whenLoaded('tdrPrefaisabilite', function () {
                return $this->tdrPrefaisabilite->first() ? new TdrResource($this->tdrPrefaisabilite->first()) : null;
            }),
            'fichiers_tdr_prefaisabilite' => $this->tdrPrefaisabilite?->first()?->fichiers ? [
                'fichier_tdr' => $this->tdrPrefaisabilite->first()->fichiers->where('metadata.type_document', 'tdr-prefaisabilite')->first()
                    ? new FichierResource($this->tdrPrefaisabilite->first()->fichiers->where('metadata.type_document', 'tdr-prefaisabilite')->first())
                    : null,
                'autres_documents' => FichierResource::collection(
                    $this->tdrPrefaisabilite->first()->fichiers->where('metadata.type_document', 'autre-document-prefaisabilite')->values()
                ),
            ] : ['fichier_tdr' => null, 'autres_documents' => []],

            'tdr_faisabilite' => $this->whenLoaded('tdrFaisabilite', function () {
                return $this->tdrFaisabilite->first() ? new TdrResource($this->tdrFaisabilite->first()) : null;
            }),
            'fichiers_tdr_faisabilite' => $this->tdrFaisabilite?->first()?->fichiers ? [
                'fichier_tdr' => $this->tdrFaisabilite->first()->fichiers->where('metadata.type_document', 'tdr-faisabilite')->first()
                    ? new FichierResource($this->tdrFaisabilite->first()->fichiers->where('metadata.type_document', 'tdr-faisabilite')->first())
                    : null,
                'autres_documents' => FichierResource::collection(
                    $this->tdrFaisabilite->first()->fichiers->where('metadata.type_document', 'autre-document-faisabilite')->values()
                ),
            ] : ['fichier_tdr' => null, 'autres_documents' => []],

            // Rapport faisabilite preliminaire (etude de profil)
            'rapport_faisabilite_preliminaire' => $this->when($this->est_mou, function () {
                return $this->whenLoaded('rapportFaisabilitePreliminaire', function () {
                    return $this->rapportFaisabilitePreliminaire->first() ? new RapportResource($this->rapportFaisabilitePreliminaire->first()) : null;
                });
            }),
            'fichiers_rapport_faisabilite_preliminaire' => $this->when($this->est_mou, function () {
                $rapport = $this->rapportFaisabilitePreliminaire?->first();
                return $rapport?->fichiers ? [
                    'fichiers_rapport' => FichierResource::collection($rapport->fichiers->where('categorie', 'rapport-faisabilite-preliminaire')->values()),
                    'proces_verbaux' => FichierResource::collection($rapport->fichiers->where('categorie', 'proces-verbal')->values()),
                    'liste_presence' => $rapport->fichiers->where('categorie', 'liste-presence')->first()
                        ? new FichierResource($rapport->fichiers->where('categorie', 'liste-presence')->first())
                        : null,
                    'documents_annexes' => FichierResource::collection($rapport->fichiers->where('categorie', 'document-annexe')->values())
                ] : [
                    'fichiers_rapport' => [],
                    'proces_verbaux' => [],
                    'liste_presence' => null,
                    'documents_annexes' => []
                ];
            }),

            // Rapports etudes (prefaisabilite et faisabilite)
            'rapport_prefaisabilite' => $this->whenLoaded('rapportPrefaisabilite', function () {
                return $this->rapportPrefaisabilite->first() ? new RapportResource($this->rapportPrefaisabilite->first()) : null;
            }),
            'fichiers_rapport_prefaisabilite' => $this->rapportPrefaisabilite?->first()?->fichiers ? [
                'fichiers_rapport' => FichierResource::collection($this->rapportPrefaisabilite->first()->fichiers->where('categorie', 'rapport-prefaisabilite')->values()),
                'proces_verbaux' => FichierResource::collection($this->rapportPrefaisabilite->first()->fichiers->where('categorie', 'proces-verbal')->values()),
                'liste_presence' => $this->rapportPrefaisabilite->first()->fichiers->where('categorie', 'liste-presence')->first()
                    ? new FichierResource($this->rapportPrefaisabilite->first()->fichiers->where('categorie', 'liste-presence')->first())
                    : null,
                'documents_annexes' => FichierResource::collection($this->rapportPrefaisabilite->first()->fichiers->where('categorie', 'document-annexe')->values())
            ] : [
                'fichiers_rapport' => [],
                'proces_verbaux' => [],
                'liste_presence' => null,
                'documents_annexes' => []
            ],

            'rapport_faisabilite' => $this->whenLoaded('rapportFaisabilite', function () {
                return $this->rapportFaisabilite->first() ? new RapportResource($this->rapportFaisabilite->first()) : null;
            }),
            'fichiers_rapport_faisabilite' => $this->rapportFaisabilite?->first()?->fichiers ? [
                'fichiers_rapport' => FichierResource::collection($this->rapportFaisabilite->first()->fichiers->where('categorie', 'rapport-faisabilite')->values()),
                'proces_verbaux' => FichierResource::collection($this->rapportFaisabilite->first()->fichiers->where('categorie', 'proces-verbal')->values()),
                'liste_presence' => $this->rapportFaisabilite->first()->fichiers->where('categorie', 'liste-presence')->first()
                    ? new FichierResource($this->rapportFaisabilite->first()->fichiers->where('categorie', 'liste-presence')->first())
                    : null,
                'documents_annexes' => FichierResource::collection($this->rapportFaisabilite->first()->fichiers->where('categorie', 'document-annexe')->values()),
            ] : [
                'fichiers_rapport' => [],
                'proces_verbaux' => [],
                'liste_presence' => null,
                'documents_annexes' => [],
            ],

            'rapport_evaluation_ex_ante' => $this->whenLoaded('rapportEvaluationExAnte', function () {
                return $this->rapportEvaluationExAnte->first() ? new RapportResource($this->rapportEvaluationExAnte->first()) : null;
            }),
            'fichiers_rapport_evaluation_ex_ante' => $this->rapportEvaluationExAnte?->first()?->fichiers ? [
                'fichiers_rapport' => FichierResource::collection($this->rapportEvaluationExAnte->first()->fichiers->where('categorie', 'rapport-evaluation-ex-ante')->values()),
                'proces_verbaux' => FichierResource::collection($this->rapportEvaluationExAnte->first()->fichiers->where('categorie', 'proces-verbal')->values()),
                'liste_presence' => $this->rapportEvaluationExAnte->first()->fichiers->where('categorie', 'liste-presence')->first()
                    ? new FichierResource($this->rapportEvaluationExAnte->first()->fichiers->where('categorie', 'liste-presence')->first())
                    : null,
                'documents_annexes' => FichierResource::collection($this->rapportEvaluationExAnte->first()->fichiers->where('categorie', 'document-annexe')->values()),
            ] : [
                'fichiers_rapport' => [],
                'proces_verbaux' => [],
                'liste_presence' => null,
                'documents_annexes' => [],
            ],

            'cibles' => $this->whenLoaded('cibles', CibleResource::collection($this->cibles)),
            'odds' => $this->whenLoaded('odds', OddResource::collection($this->odds)),

            'sources_de_financement' => $this->whenLoaded('sources_de_financement', FinancementResource::collection($this->sources_de_financement)),

            'financements' => $this->types_financement(),

            'composants' => $this->composants->map(function ($composant) {
                return [
                    'id' => $composant->hashed_id,
                    'intitule' => $composant->intitule,
                    'type_programme' => $composant->typeProgramme?->hashed_id ?? null
                ];
            }),

            'programmes' => $this->programmes(),

            'lieux_intervention' => LieuInterventionResource::collection($this->lieuxIntervention),

            'types_intervention' => $this->whenLoaded('typesIntervention', function () {
                return $this->typesIntervention->map(function ($type) {
                    return [
                        'id' => $type->hashed_id,
                        'nom' => $type->nom
                    ];
                });
            }),

            /*'tdrs_prefaisabilite' => [
                "document" => $this->tdrs_prefaisabilite,
                "resume" => $this->resume_tdr_prefaisabilite
            ],

            'tdrs_faisabilite' => [
                "document" => $this->tdrs_faisabilite,
                "resume" => $this->resume_tdr_faisabilite
            ],

            'rapports_prefaisabilite' => [
                "document" => $this->rapports_prefaisabilite,
                "cabinet_etude" => $this->info_cabinet_etude_faisabilite
            ],

            'rapports_faisabilite' => [
                "document" => $this->rapports_faisabilite,
                "cabinet_etude" => $this->info_cabinet_etude_faisabilite
            ],

            'rapports_prefaisabilite' => [
                "document" => $this->rapports_prefaisabilite,
                "cabinet_etude" => $this->info_cabinet_etude_faisabilite
            ],

            'rapports_evaluation_ex_ante' => [
                "rapports_evaluation_ex_ante" => $this->rapports_evaluation_ex_ante,
                "documents_annexe_rapports_evaluation_ex_ante" => $this->documents_annexe_rapports_evaluation_ex_ante
            ],
            */

            // Timestamps
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d H:i:s"),
            'updated_at' => Carbon::parse($this->updated_at)->format("Y-m-d H:i:s"),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return array_merge(parent::with($request), [
            'meta' => [
                'type' => 'ideeprojet',
                'version' => '1.0',
            ],
        ]);
    }
}
