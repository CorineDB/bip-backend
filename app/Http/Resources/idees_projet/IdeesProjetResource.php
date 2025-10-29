<?php

namespace App\Http\Resources\idees_projet;

use App\Http\Resources\BaseApiResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IdeesProjetResource extends BaseApiResource
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
            'porteur_projet' => $this->porteur_projet,
            'est_un_projet' => $this->projet ? true : false,
            'projetId' => $this->when($this->projet, function(){
                return $this->projet->hashed_id;
            }),

            // Statuts et phases
            'statut' => $this->statut?->value ?? $this->statut,
            // Statuts et phases
            'statut' => $this->statut?->value ?? $this->statut,
            'phase' => $this->phase?->value ?? $this->phase,
            'sous_phase' => $this->sous_phase?->value ?? $this->sous_phase,
            'type_projet' => $this->type_projet?->value ?? $this->type_projet,
            'est_coherent' => $this->est_coherent,
            'est_soumise' => $this->est_soumise,

            // Scores d'évaluation
            'score_climatique' => $this->score_climatique,
            'score_amc' => $this->score_amc,
            'score_pertinence' => $this->score_pertinence,

            // Descriptions et contenus principaux
            'description_projet' => $this->description_projet,
            // Détails techniques et organisationnels
            'duree' => $this->duree,
            // Données JSON structurées
            'cout_estimatif_projet' => $this->cout_estimatif_projet ?? [],
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d H:i:s"),
            'updated_at' => Carbon::parse($this->updated_at)->format("Y-m-d H:i:s"),

            'historique_des_evaluations_de_pertinence' => $this->whenLoaded('historiqueEvaluationsPertinence', function () {
                return $this->historiqueEvaluationsPertinence->map(function ($evaluation) {
                    return [
                        'id' => $evaluation->hashed_id,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? \Carbon\Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? \Carbon\Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? \Carbon\Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->validator?->hashed_id, //$evaluation->valider_par,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $evaluation->resultats_evaluation,
                        'statut' => $evaluation->statut
                    ];
                });
            }),

            'historique_des_evaluations_climatique' => $this->whenLoaded('historiqueEvaluationsClimatique', function () {
                return $this->historiqueEvaluationsClimatique->map(function ($evaluation) {
                    return [
                        'id' => $evaluation->hashed_id,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? \Carbon\Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? \Carbon\Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? \Carbon\Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->validator?->hashed_id, //$evaluation->valider_par,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $evaluation->resultats_evaluation,
                        'statut' => $evaluation->statut
                    ];
                });
            }),

            'historique_des_analyse_multi_critere' => $this->whenLoaded('historiqueEvaluationsAMC', function () {
                return $this->historiqueEvaluationsAMC->map(function ($evaluation) {
                    return [
                        'id' => $evaluation->hashed_id,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? \Carbon\Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? \Carbon\Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? \Carbon\Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->validator?->hashed_id, //$evaluation->valider_par,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $evaluation->resultats_evaluation,
                        'statut' => $evaluation->statut
                    ];
                });
            }),
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
