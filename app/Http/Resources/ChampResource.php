<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ChampResource extends BaseApiResource
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
            'id'              => $this->id,
            'label'           => $this->label,
            'info'            => $this->info,
            'attribut'        => $this->attribut,
            'placeholder'     => $this->placeholder,
            'is_required'     => $this->is_required,
            'default_value'   => $this->default_value,
            'isEvaluated'     => $this->isEvaluated,
            'commentaire'     => $this->commentaire,
            'ordre_affichage' => $this->ordre_affichage,
            'type_champ'      => $this->type_champ,
            'secteurId'       => $this->secteurId,
            'meta_options'    => $this->meta_options,
            'champ_config'    => $this->champ_config,
            'valeur_config'   => $this->valeur_config,
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
                'type' => 'champ',
                'version' => '1.0',
            ],
        ]);
    }
}