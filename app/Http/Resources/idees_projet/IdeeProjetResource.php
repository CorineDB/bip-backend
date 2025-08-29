<?php

namespace App\Http\Resources\idees_projet;

use App\Http\Resources\BaseApiResource;
use App\Http\Resources\CibleResource;
use App\Http\Resources\FinancementResource;
use App\Http\Resources\LieuInterventionResource;
use App\Http\Resources\OddResource;
use Illuminate\Http\Request;

class IdeeProjetResource extends BaseApiResource
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

            // Statuts et phases
            'statut' => $this->statut?->value ?? $this->statut,
            'phase' => $this->phase?->value ?? $this->phase,
            'sous_phase' => $this->sous_phase?->value ?? $this->sous_phase,
            'type_projet' => $this->type_projet?->value ?? $this->type_projet,
            'est_coherent' => $this->est_coherent,
            'est_soumise' => $this->est_soumise,

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
            'parties_prenantes' => $this->parties_prenantes ?? [],
            'objectifs_specifiques' => $this->objectifs_specifiques ?? [],
            'resultats_attendus' => $this->resultats_attendus ?? [],
            'body_projet' => $this->body_projet ?? [],
            'description_decision' => $this->description_decision,

            'champs' => $this->champs->map(function ($champ) {
                return [
                    'id' => $champ->id,
                    'attribut' => $champ->attribut,
                    'value' => $champ->pivot->valeur
                ];
            }),
            // Relations principales (loaded when needed)
            'secteur' => $this->whenLoaded('secteur'),
            'ministere' => $this->whenLoaded('ministere'),
            'categorie' => $this->whenLoaded('categorie'),
            'responsable' => $this->whenLoaded('responsable'),
            'demandeur' => $this->whenLoaded('demandeur'),
            'porteur_projet' => $this->porteur_projet,

            'cibles' => $this->whenLoaded('cibles', CibleResource::collection($this->cibles)),
            'odds' => $this->whenLoaded('odds', OddResource::collection($this->odds)),

            'sources_de_financement' => $this->whenLoaded('sources_de_financement', FinancementResource::collection($this->sources_de_financement)),

            'composants' => $this->composants->map(function ($composant) {
                    return [
                        'id' => $composant->id,
                        'intitule' => $composant->intitule,
                        'type_programme' => $composant->typeProgramme->id ?? null
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

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
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
