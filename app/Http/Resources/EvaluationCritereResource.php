<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class EvaluationCritereResource extends BaseApiResource
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
            'note' => $this->note,
            'commentaire' => $this->commentaire,
            'is_completed' => $this->isCompleted(),
            'is_pending' => $this->isPending(),
            'status' => $this->status,
            'numeric_value' => $this->getNumericValue(),
            
            // Relations
            'evaluateur' => $this->whenLoaded('evaluateur', function () {
                return [
                    'id' => $this->evaluateur->id,
                    'username' => $this->evaluateur->username,
                    'email' => $this->evaluateur->email,
                ];
            }),
            
            'critere' => $this->whenLoaded('critere', function () {
                return [
                    'id' => $this->critere->id,
                    'nom' => $this->critere->nom,
                    'description' => $this->critere->description,
                ];
            }),
            
            'notation' => $this->whenLoaded('notation', function () {
                return [
                    'id' => $this->notation->id,
                    'libelle' => $this->notation->libelle,
                    'valeur' => $this->notation->valeur,
                    'commentaire' => $this->notation->commentaire,
                ];
            }),
            
            'categorie_critere' => $this->whenLoaded('categorieCritere', function () {
                return [
                    'id' => $this->categorieCritere->id,
                    'nom' => $this->categorieCritere->nom,
                    'description' => $this->categorieCritere->description,
                ];
            }),
            
            'evaluation' => $this->whenLoaded('evaluation', function () {
                return [
                    'id' => $this->evaluation->id,
                    'type_evaluation' => $this->evaluation->type_evaluation,
                    'date_debut_evaluation' => $this->evaluation->date_debut_evaluation,
                ];
            }),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
                'type' => 'evaluationcritere',
                'version' => '1.0',
            ],
        ]);
    }
}