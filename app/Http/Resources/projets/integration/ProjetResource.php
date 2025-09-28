<?php

namespace App\Http\Resources\projets\integration;

use App\Http\Resources\BaseApiResource;
use App\Http\Resources\CategorieProjetResource;
use App\Http\Resources\CibleResource;
use App\Http\Resources\EvaluationResource;
use App\Http\Resources\FinancementResource;
use App\Http\Resources\idees_projet\IdeeProjetResource;
use App\Http\Resources\LieuInterventionResource;
use App\Http\Resources\NoteConceptuelleResource;
use App\Http\Resources\OddResource;
use App\Http\Resources\OrganisationResource;
use App\Http\Resources\RapportResource;
use App\Http\Resources\SecteurResourcePublic;
use App\Http\Resources\TdrResource;
use App\Http\Resources\UserResource;
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
            'id' => $this->id,
            // Identifiants et métadonnées
            'identifiant_bip' => $this->identifiant_bip,
            'identifiant_sigfp' => $this->identifiant_sigfp,
            'sigle' => $this->sigle,
            'titre_projet' => $this->titre_projet,
            'est_a_haut_risque' => $this->est_a_haut_risque,
            'est_dur' => $this->est_dur,
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
            /*

            'champs' => $this->champs->map(function ($champ) {
                return [
                    'id' => $champ->id,
                    'attribut' => $champ->attribut,
                    'value' => $champ->pivot->valeur
                ];
            }),
            */
            // Relations principales (loaded when needed)
            'secteur' => new SecteurResourcePublic($this->secteur),
            'responsable' => new UserResource($this->responsable),
            'demandeur' => new UserResource($this->demandeur),
            'categorie' => new CategorieProjetResource($this->categorie),
            'ministere' => new OrganisationResource($this->ministere),

            /*'ministere' => $this->whenLoaded('ministere'),
            'categorie' => $this->whenLoaded('categorie'),
            'responsable' => $this->whenLoaded('responsable'),
            'demandeur' => $this->whenLoaded('demandeur'),*/

            'porteur_projet' => $this->porteur_projet,
            //'ideeProjet' => new IdeeProjetResource($this->ideeProjet),

            'evaluationClimatique' => $this->evaluationClimatique->first() ? new EvaluationResource($this->evaluationClimatique->first()) : null,
            'evaluationAmc' => $this->evaluationAMC->first() ? new EvaluationResource($this->evaluationAMC->first()) : null,

            'noteConceptuelle' => array_merge(
                (new NoteConceptuelleResource($this->noteConceptuelle))->toArray(request()),
                [
                    'appreciation' => $this->noteConceptuelle->evaluationTermine()
                        ? new EvaluationResource($this->noteConceptuelle->evaluationTermine())
                        : [],
                ]
            ),

            // TDRs
            'tdr_prefaisabilite' => /*$this->whenLoaded('tdrPrefaisabilite', function() {
                return $this->tdrPrefaisabilite->first() ? (new TdrResource($this->tdrPrefaisabilite->first()))
                ->additional([
                    'appreciation' => $this->tdrPrefaisabilite->first()->evaluationPrefaisabiliteTerminer() ? new EvaluationResource($this->tdrPrefaisabilite->first()->evaluationPrefaisabiliteTerminer()) : null,
                ]) : null;
            })*/
            $this->tdrPrefaisabilite->first()
                ? array_merge(
                    (new TdrResource($this->tdrPrefaisabilite->first()))->toArray(request()),
                    [
                        'appreciation' => $this->tdrPrefaisabilite->first()->evaluationPrefaisabiliteTerminer()
                            ? new EvaluationResource($this->tdrPrefaisabilite->first()->evaluationPrefaisabiliteTerminer())
                            : null,
                    ]
                )
                : null,

            'tdr_faisabilite' => /* $this->whenLoaded('tdrFaisabilite', function() {
                return $this->tdrFaisabilite->first() ? (new TdrResource($this->tdrFaisabilite->first()))
                ->additional([
                    'appreciation' => $this->tdrFaisabilite->first()->evaluationFaisabiliteTerminer() ? new EvaluationResource($this->tdrFaisabilite->first()->evaluationFaisabiliteTerminer()) : null,
                ]) : null;
            }),*/

            $this->tdrFaisabilite->first()
                ? array_merge(
                    (new TdrResource($this->tdrFaisabilite->first()))->toArray(request()),
                    [
                        'appreciation' => $this->tdrFaisabilite->first()->evaluationPrefaisabiliteTerminer()
                            ? new EvaluationResource($this->tdrFaisabilite->first()->evaluationPrefaisabiliteTerminer())
                            : null,
                    ]
                )
                : null,

            // Rapports etudes (prefaisabilite et faisabilite)
            'rapport_prefaisabilite' => /* $this->whenLoaded('rapportPrefaisabilite', function() {
                return */ $this->rapportPrefaisabilite->first() ? new RapportResource($this->rapportPrefaisabilite->first()->load(['fichiersRapport', 'procesVerbaux', 'documentsAnnexes'])) : null/* ;
            }) */,

            'rapport_faisabilite' => /* $this->whenLoaded('rapportFaisabilite', function() {
                return */ $this->rapportFaisabilite->first() ? new RapportResource($this->rapportFaisabilite->first()) : null/* ;
            }) */,

            'rapport_evaluation_ex_ante' => /* $this->whenLoaded('rapportEvaluationExAnte', function() {
                return */ $this->rapportEvaluationExAnte->first() ? new RapportResource($this->rapportEvaluationExAnte->first()->load(['fichiersRapport', 'documentsAnnexes'])) : null/* ;
            }) */,

            'cibles' => /* $this->whenLoaded('cibles',*/ CibleResource::collection($this->cibles)/* ) */,
            'odds' => /* $this->whenLoaded('odds', */ OddResource::collection($this->odds)/* ) */,

            'sources_de_financement' => /* $this->whenLoaded('sources_de_financement',  */ FinancementResource::collection($this->sources_de_financement)/* ) */,

            'orientations_strategique_png' => $this->orientations_strategique_png,
            'objectifs_strategique_png' => $this->objectifs_strategique_png,
            'resultats_strategique_png' => $this->resultats_strategique_png,
            'piliers_pag' => $this->pilliers_pag,
            'axes_pag' => $this->axes_pag,
            'actions_pag' => $this->actions_pag,
            'composants' => $this->composants->map(function ($composant) {
                return [
                    'id' => $composant->id,
                    'intitule' => $composant->intitule,
                    'type_programme_id' => $composant->typeProgramme->id ?? null,
                    'intitule_composant_programme' => $composant->typeProgramme->type_programme ?? null
                ];
            }),

            'lieux_intervention' => LieuInterventionResource::collection($this->lieuxIntervention),

            'types_intervention' => $this->whenLoaded('typesIntervention', function () {
                return $this->typesIntervention->map(function ($type) {
                    return [
                        'id' => $type->id,
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
            ],*/

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
