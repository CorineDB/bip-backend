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
            'type_evaluation' => $this->type_evaluation,
            'id_evaluation' => $this->id_evaluation,
            'date_debut_evaluation' => Carbon::parse($this->date_debut_evaluation)->format("d/m/Y H:m:i"),
            'date_fin_evaluation' => Carbon::parse($this->date_fin_evaluation)->format("d/m/Y H:m:i"),
            'valider_le' => Carbon::parse($this->valider_le)->format("d/m/Y H:m:i"),
            'valider_par' => $this->valider_par,
            'commentaire' => $this->commentaire,
            'id_evaluation' => $this->id_evaluation,
            'evaluation' => $this->evaluation,
            'resultats_evaluation' => $this->resultats_evaluation,
            'statut' => $this->statut,
            'evaluateurs' => $this->getActiveEvaluationsByUser()->mapWithKeys(function($evaluateur){
                //$evaluateur = User::find($evaluateur);
                return $evaluateur;
                return [
                    'id' => $evaluateur->id,
                    'nom_complet' => $evaluateur->personne->nom . " " . $evaluateur->personne->prenom,
                    'email' => $evaluateur->email,
                    "evaluation_individuel" => $evaluationIndividuel
                ];

            }),

            'historique_evaluations' => $this->whenLoaded("historique_evaluations", function(){
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
