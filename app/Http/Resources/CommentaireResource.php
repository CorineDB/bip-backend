<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CommentaireResource extends BaseApiResource
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
            'commentaire' => $this->commentaire,
            'commentaire_id' => $this->commentaire_id,
            'date' => $this->date?->format('Y-m-d H:i:s'),

            // Informations sur le commentateur
            'commentateur' => $this->when($this->relationLoaded('commentateur'), function() {
                return $this->commentateur ? [
                    'id' => $this->commentateur->id,
                    'name' => $this->commentateur->nom,
                    'email' => $this->commentateur->email,
                ] : null;
            }),

            // Fichiers attachés au commentaire
            'fichiers' => $this->when($this->relationLoaded('fichiers'), function() {
                return FichierResource::collection($this->fichiers);
            }),

            // Réponses (sous-commentaires)
            'reponses' => $this->when($this->relationLoaded('enfants'), function() {
                return $this->enfants->map(function($enfant) {
                    return [
                        'id' => $enfant->id,
                        'commentaire' => $enfant->commentaire,
                        'date' => $enfant->date?->format('Y-m-d H:i:s'),
                        'commentateur' => $enfant->commentateur ? [
                            'id' => $enfant->commentateur->id,
                            'name' => $enfant->commentateur->name,
                            'email' => $enfant->commentateur->email,
                        ] : null,
                        'fichiers' => $enfant->relationLoaded('fichiers') ? FichierResource::collection($enfant->fichiers) : [],
                        'nb_fichiers' => $enfant->relationLoaded('fichiers') ? $enfant->fichiers->count() : 0,
                        'parent' => [
                            'id' => $this->id,
                            'commentaire' => $this->commentaire,
                        ],
                        'created_at' => $enfant->created_at?->format('Y-m-d H:i:s'),
                        'updated_at' => $enfant->updated_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),

            // Parent (si c'est une réponse)
            'parent' => $this->when($this->parent !== null, function() {
                return $this->parent ? [
                    'id' => $this->parent->id,
                    'commentaire' => $this->parent->commentaire,
                    'date' => $this->parent->date?->format('Y-m-d H:i:s'),
                    'commentateur' => $this->parent->commentateur ? [
                        'id' => $this->parent->commentateur->id,
                        'name' => $this->parent->commentateur->name,
                        'email' => $this->parent->commentateur->email,
                    ] : null,
                ] : null;
            }),

            // Ressource commentée
            'ressource_commentee' => [
                'type' => class_basename($this->commentaireable_type),
                'id' => $this->commentaireable_id,
            ],

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
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
                'type' => 'commentaire',
                'version' => '1.0',
            ],
        ]);
    }
}
