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
            'id'                => $this->hashed_id,
            'label'             => $this->label,
            'info'              => $this->info,
            'key'               => $this->attribut,
            'attribut'          => $this->attribut,
            'placeholder'       => $this->placeholder,
            'is_required'       => (bool) $this->is_required,
            'default_value'     => $this->default_value,
            'isEvaluated'       => (bool) $this->isEvaluated,
            'ordre_affichage'   => (int) $this->ordre_affichage,
            'type_champ'        => $this->type_champ,
            'sectionId'         => $this->section?->hashed_id,
            'documentId'        => $this->document?->hashed_id,
            'meta_options'      => $this->meta_options ?: (object)[],
            'champ_standard'    => (bool) $this->champ_standard,
            'startWithNewLine'  => $this->startWithNewLine
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
