<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EvaluationResource extends BaseApiResource
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
            'type_evaluation' => $this->type_evaluation,
            'date_debut_evaluation' => Carbon::parse($this->date_debut_evaluation)->format("d/m/Y H:m:i"),
            'date_fin_evaluation' => Carbon::parse($this->date_fin_evaluation)->format("d/m/Y H:m:i"),
            'valider_le' => Carbon::parse($this->valider_le)->format("d/m/Y H:m:i"),
            'valider_par' => $this->validator?->hashed_id, //valider_par,
            'commentaire' => $this->commentaire,
            'id_evaluation' => $this->parentEvaluation?->hashed_id,
            'evaluation' => $this->evaluation,
            'resultats_evaluation' => $this->resultats_evaluation,
            'statut' => $this->statut,
            /* 'evaluateurs' => $this->getActiveEvaluationsByUser()->mapWithKeys(function($evaluateur){
                //return $evaluateur;
                return [
                    'id' => $evaluateur->hashed_id,
                    'nom_complet' => $evaluateur->evaluateur->personne->nom . " " . $evaluateur->evaluateur->personne->prenom,
                    'email' => $evaluateur->email,
                    //"evaluation_individuel" => $evaluationIndividuel
                ];

            }), */
            'evaluateurs' => $this->getActiveEvaluationsByUser()->map(function ($evaluations, $evaluateurId) {
                // on prend la première évaluation pour obtenir les infos de l’évaluateur
                $firstEvaluation = $evaluations->first();
                $evaluateur = $firstEvaluation->evaluateur;

                return [
                    'id' => $evaluateur->hashed_id,
                    'nom_complet' => optional($evaluateur->personne)->nom . ' ' . optional($evaluateur->personne)->prenom,
                    'email' => $evaluateur->email,
                    'evaluations' => $evaluations->map(function ($evaluation) {
                        return [
                            'id' => $evaluation->hashed_id,
                            'note' => $evaluation->note,
                            'commentaire' => $evaluation->commentaire,
                            'evaluation_evaluateur_id' => $evaluation->evaluation_evaluateur_id,
                            'evaluateur_id' => $evaluation->evaluateur->hashed_id,

                            'notation_id' => $evaluation->notation?->hashed_id,
                            'critere_id' => $evaluation->critere?->hashed_id,
                            "categorie_critere_id" => $evaluation?->critere?->categorie_critere?->hashed_id,
                            'evaluation_id' => $evaluation->hashed_id,
                            'created_at' => Carbon::parse($evaluation->created_at)->format("Y-m-d H:i:s"),
                            'updated_at' => Carbon::parse($evaluation->created_at)->format("Y-m-d H:i:s"),
                            'deleted_at' => Carbon::parse($evaluation->created_at)->format("Y-m-d H:i:s"),

                            'is_auto_evaluation' => $evaluation->is_auto_evaluation,
                            'est_archiver' => $evaluation->est_archiver,

                            'evaluateur' => [
                                'id' => $evaluation?->evaluateur?->hashed_id,
                                'nom' => $evaluation?->evaluateur?->personne?->nom,
                                'prenom' => $evaluation?->evaluateur?->personne?->prenom,
                                'poste' => $evaluation?->evaluateur?->personne?->critere?->poste,
                            ],

                            'critere' => [
                                'id' => $evaluation?->critere?->hashed_id,
                                'intitule' => $evaluation?->critere?->intitule,
                                'ponderation' => $evaluation?->critere?->ponderation,
                                'commentaire' => $evaluation?->critere?->commentaire,
                                'is_mandatory' => $evaluation?->critere?->is_mandatory,
                                'created_at' => $evaluation?->critere?->created_at,
                                'updated_at' => $evaluation?->critere?->updated_at,
                            ],
                            'notation' => [
                                'id' => $evaluation?->notation?->hashed_id,
                                'libelle' => $evaluation?->notation?->libelle,
                                'valeur' => $evaluation?->notation?->valeur,
                            ],
                            'categorie_critere' => [
                                'id' => $evaluation?->categorieCritere?->hashed_id,
                                'type' => $evaluation?->categorieCritere?->type,
                            ],
                        ];
                    }),
                ];
            })->values(),


            'historique_evaluations' => $this->whenLoaded("historique_evaluations", function () {
                return EvaluationResource::collection($this->historique_evaluations);
            })
        ];
        return parent::toArray($request);
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
                'type' => 'evaluation',
                'version' => '1.0',
            ],
        ]);
    }
}
