<?php

namespace App\Http\Resources;

use App\Models\User;
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
            'date_debut_evaluation' => $this->date_debut_evaluation,
            'date_fin_evaluation' => $this->date_fin_evaluation,
            'valider_le' => $this->valider_le,
            'valider_par' => $this->valider_par,
            'commentaire' => $this->commentaire,
            'evaluation' => $this->evaluation,
            'resultats_evaluation' => $this->resultats_evaluation,
            'statut' => $this->statut,
            'evaluateurs' => $this->getEvaluationsByUser()->mapWithKeys(function($evaluateur){
                //$evaluateur = User::find($evaluateur);
                return $evaluateur;
                return [
                    'id' => $evaluateur->id,
                    'nom_complet' => $evaluateur->personne->nom . " " . $evaluateur->personne->prenom,
                    'email' => $evaluateur->email,
                    "evaluation_individuel" => $evaluationIndividuel
                ];

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