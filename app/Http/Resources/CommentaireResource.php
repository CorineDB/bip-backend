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
        $data = parent::toArray($request);

        // Ajouter les relations si elles sont chargées
        if ($this->relationLoaded('commentateur')) {
            $data['commentateur'] = $this->commentateur ? [
                'id' => $this->commentateur->id,
                'name' => $this->commentateur->name,
                'email' => $this->commentateur->email,
            ] : null;
        }

        // Ajouter les fichiers attachés
        if ($this->relationLoaded('fichiers')) {
            $data['fichiers'] = FichierResource::collection($this->fichiers);
            //$data['nb_fichiers'] = $this->fichiers->count();
        }

        // Ajouter les réponses (sous-commentaires)
        if ($this->relationLoaded('enfants')) {
            $data['reponses'] = static::collection($this->enfants);
            //$data['nb_reponses'] = $this->enfants->count();
        }

        // Ajouter le parent si c'est une réponse
        if ($this->relationLoaded('parent')) {
            $data['parent'] = $this->parent ? new static($this->parent) : null;
        }

        // Ajouter la ressource commentée si elle est chargée
        if ($this->relationLoaded('commentaireable')) {
            $data['ressource_commentee'] = $this->commentaireable ? [
                'type' => class_basename($this->commentaireable_type),
                'id' => $this->commentaireable_id,
            ] : null;
        }

        return $data;
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
