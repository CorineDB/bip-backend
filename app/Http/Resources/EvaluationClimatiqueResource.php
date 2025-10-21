<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationClimatiqueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hashed_id,
            'type_evaluation' => $this->type_evaluation,
            'date_debut_evaluation' => $this->date_debut_evaluation,
            'date_fin_evaluation' => $this->date_fin_evaluation,
            'valider_le' => $this->valider_le,
            'commentaire' => $this->commentaire,
            'resultats_evaluation' => $this->resultats_evaluation,
            'statut' => $this->valider_le ? 'finalisée' : 'en_cours',

            // Relations
            'idee_projet' => [
                'id' => $this->projetable->hashed_id ?? null,
                'sigle' => $this->projetable->sigle ?? null,
                'titre_projet' => $this->projetable->titre_projet ?? null,
            ],

            'evaluateur_principal' => $this->evaluateur ? [
                'id' => $this->evaluateur->hashed_id,
                'nom' => $this->evaluateur->nom,
                'prenom' => $this->evaluateur->prenom,
                'email' => $this->evaluateur->email,
            ] : null,

            'evaluateurs' => UserResource::collection($this->whenLoaded('evaluateurs')),

            'criteres_evaluation' => EvaluationCritereResource::collection($this->whenLoaded('evaluationCriteres')),

            // Statistiques
            'statistiques' => [
                'total_criteres' => $this->evaluationCriteres()->count(),
                'criteres_completes' => $this->evaluationCriteres()
                    ->whereNotNull('notation_id')
                    ->where('note', '!=', 'En attente')
                    ->count(),
                'pourcentage_completion' => $this->calculateCompletionPercentage(),
                'nombre_evaluateurs' => $this->evaluateurs()->count(),
            ],

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Calcul du pourcentage de completion
     */
    private function calculateCompletionPercentage(): float
    {
        $total = $this->evaluationCriteres()->count();
        $completed = $this->evaluationCriteres()
            ->whereNotNull('notation_id')
            ->where('note', '!=', 'En attente')
            ->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }
}
