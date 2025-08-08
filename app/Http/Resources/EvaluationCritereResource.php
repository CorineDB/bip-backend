<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
                    'nom_complet' => $this->evaluateur->personne->nom . " " . $this->evaluateur->personne->prenom,
                    'email' => $this->evaluateur->email,
                ];
            }),

            'critere' => $this->whenLoaded('critere', function () {
                return [
                    'id' => $this->critere->id,
                    'intitule' => $this->critere->intitule,
                    'ponderation' => $this->critere->ponderation,
                    'ponderation_pct' => $this->critere->ponderation . '%',
                    'commentaire' => $this->critere->commentaire,
                    'is_mandatory' => $this->critere->is_mandatory,
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

            'created_at' => Carbon::parse($this->created_at)->format("d/m/Y H:m:i"),
            'updated_at' => Carbon::parse($this->updated_at)->format("d/m/Y H:m:i"),
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