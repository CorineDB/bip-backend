<?php

namespace App\Http\Resources;

use App\Models\CategorieDocument;
use Illuminate\Http\Request;

class CanevasAppreciationRapportExAnteResource extends BaseApiResource
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
            'id'                    => $this->hashed_id,
            'nom'                   => $this->nom,
            'slug'                   => $this->slug,
            'description'           => $this->description,
            'type'                  => $this->type,
            'categorie'             => new CategorieDocumentResource($this->categorie),
            'evaluation_configs'    => $this->evaluation_configs,
            'forms'                 => $this->buildFormsStructure()
        ];
    }

    /**
     * Construire la structure forms récursive sans exposer les clés internes
     */
    private function buildFormsStructure()
    {
        $forms = collect();

        // Ajouter les champs racines (sans section)
        foreach ($this->champs->sortBy('ordre_affichage') as $champ) {
            $forms->push($this->buildFieldElement($champ));
        }

        // Ajouter les sections racines (sans parent)
        foreach ($this->sections->whereNull('parentSectionId')->sortBy('ordre_affichage') as $section) {
            $forms->push($this->buildSectionElement($section));
        }

        // Trier tous les éléments par ordre d'affichage
        return $forms->sortBy('ordre_affichage')->values();
    }

    /**
     * Construire un élément field pour la structure forms
     */
    private function buildFieldElement($champ): array
    {
        return [
            'element_type' => 'field',
            'ordre_affichage' => $champ->ordre_affichage,
            'id' => $champ->hashed_id,
            'label' => $champ->label,
            'info' => $champ->info ?? '',
            'key' => $champ->attribut,
            'attribut' => $champ->attribut,
            'placeholder' => $champ->placeholder ?? '',
            'is_required' => $champ->is_required,
            'default_value' => $champ->default_value,
            'isEvaluated' => $champ->isEvaluated,
            'type_champ' => $champ->type_champ,
            'sectionId' => $champ->section?->hashed_id,
            'documentId' => $champ->document?->hashed_id,
            'meta_options' => $champ->meta_options ?? [],
            'champ_standard' => $champ->champ_standard,
            'startWithNewLine' => $champ->startWithNewLine
        ];
    }

    /**
     * Construire un élément section pour la structure forms de manière récursive
     */
    private function buildSectionElement($section): array
    {
        $sectionData = [
            'element_type' => 'section',
            'ordre_affichage' => $section->ordre_affichage,
            'id' => $section->hashed_id,
            'key' => $section->slug,
            'intitule' => $section->intitule,
            'description' => $section->description ?? '',
            'type' => $section->type,
            'parentSectionId' => $section->parentSection?->hashed_id,
            'elements' => []
        ];

        // Collecter tous les éléments enfants de cette section
        $elements = collect();

        // Ajouter les champs directs de cette section
        foreach ($section->champs->sortBy('ordre_affichage') as $champ) {
            $elements->push($this->buildFieldElement($champ));
        }

        // Ajouter les sous-sections de cette section
        foreach ($section->childSections->sortBy('ordre_affichage') as $sousSection) {
            $elements->push($this->buildSectionElement($sousSection));
        }

        // Trier et assigner les éléments
        $sectionData['elements'] = $elements->sortBy('ordre_affichage')->values()->toArray();

        return $sectionData;
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
                'type' => 'canevas_appreciation',
                'version' => '1.0',
                'structure' => 'forms_recursive'
            ],
        ]);
    }
}
