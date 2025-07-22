<?php

namespace App\Http\Requests\documents;

use App\Enums\EnumTypeChamp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->has('sections') && !$this->has('champs')) {
                $validator->errors()->add('sections', 'Au moins une section ou un champ doit être fourni.');
            }
        });
    }

    public function rules(): array
    {
        return [

            // Document fields
            'nom' => [
                'required',
                'string',
                'max:65535',
                Rule::unique('documents', 'nom')->whereNull('deleted_at')
            ],
            'description' => 'nullable|string|max:65535',

            'categorieId' => ['sometimes', Rule::exists('categories_document', 'id')->whereNull('deleted_at')],

            'type' => ['required', 'string', Rule::in(['document', 'formulaire', 'grille', 'checklist'])],

            // Sections
            'sections' => 'sometimes|array|min:1',
            'sections.*.intitule' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',

            'sections.*.ordre_affichage' => 'required|integer|min:1',
            'sections.*.type' => 'nullable|string|max:100',
            'sections.*.champs' => 'sometimes|array|min:1',
            'sections.*.champs.*.label' => 'required|string|max:255',
            'sections.*.champs.*.info' => 'nullable|string|max:65535',
            'sections.*.champs.*.attribut' => 'required|string|max:255',
            'sections.*.champs.*.placeholder' => 'nullable|string|max:255',
            'sections.*.champs.*.is_required' => 'boolean',
            'sections.*.champs.*.champ_standard' => 'boolean',
            'sections.*.champs.*.default_value' => 'nullable|string|max:65535',
            'sections.*.champs.*.isEvaluated' => 'boolean',
            'sections.*.champs.*.ordre_affichage' => 'required|integer|min:1',
            'sections.*.champs.*.type_champ' => ['required', 'string', Rule::in(EnumTypeChamp::values())],
            'sections.*.champs.*.meta_options' => 'required|array',
            'sections.*.champs.*.meta_options.conditions' => 'required|array|min:3',
            'sections.*.champs.*.meta_options.conditions.visible' => 'required|boolean:true',
            'sections.*.champs.*.meta_options.conditions.disable' => 'required|boolean:true',
            'sections.*.champs.*.meta_options.conditions.conditions' => 'nullable|array|min:0',

            'sections.*.champs.*.meta_options.validations_rules' => 'required|array|min:1',
            'sections.*.champs.*.meta_options.validations_rules.required' => 'required|boolean:true',
            'sections.*.champs.*.meta_options.configs' => 'required|array|min:0',

            // Champs

            'champs' => 'sometimes|array|min:1',
            'champs.*.label' => 'required|string|max:255',
            'champs.*.info' => 'nullable|string|max:65535',
            'champs.*.attribut' => 'required|string|max:255',
            'champs.*.placeholder' => 'nullable|string|max:255',
            'champs.*.is_required' => 'boolean',
            'champs.*.champ_standard' => 'boolean',
            'champs.*.default_value' => 'nullable|string|max:65535',
            'champs.*.isEvaluated' => 'boolean',
            'champs.*.ordre_affichage' => 'required|integer|min:1',
            'champs.*.type_champ' => ['required', 'string', Rule::in(EnumTypeChamp::values())],
            'champs.*.sectionId' => 'nullable|integer',
            'champs.*.meta_options' => 'required|array',
            'champs.*.meta_options.conditions' => 'required|array|min:3',
            'champs.*.meta_options.conditions.visible' => 'required|boolean:true',
            'champs.*.meta_options.conditions.disable' => 'required|boolean:true',
            'champs.*.meta_options.conditions.conditions' => 'nullable|array|min:0',

            'champs.*.meta_options.validations_rules' => 'required|array|min:1',
            'champs.*.meta_options.validations_rules.required' => 'required|boolean:true',
            'champs.*.meta_options.configs' => 'required|array|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du document est obligatoire.',
            'nom.string' => 'Le nom du document doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du document ne peut pas dépasser 65535 caractères.',
            'nom.unique' => 'Ce document existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 65535 caractères.',
            'categorieId.integer' => 'L\'identifiant de la catégorie doit être un nombre entier.',
            'categorieId.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'type.required' => 'Le type de document est obligatoire.',
            'type.string' => 'Le type de document doit être une chaîne de caractères.',
            'type.in' => 'Le type de document doit être: document, formulaire, grille ou checklist.',
            'metadata.array' => 'Les métadonnées doivent être un tableau.',
            'structure.array' => 'La structure doit être un tableau.',

            // Messages pour les sections
            'sections.array' => 'Les sections doivent être un tableau.',
            'sections.*.intitule.required' => 'L\'intitulé de la section est obligatoire.',
            'sections.*.intitule.string' => 'L\'intitulé de la section doit être une chaîne de caractères.',
            'sections.*.intitule.max' => 'L\'intitulé de la section ne peut pas dépasser 255 caractères.',
            'sections.*.ordre_affichage.required' => 'L\'ordre d\'affichage de la section est obligatoire.',
            'sections.*.ordre_affichage.integer' => 'L\'ordre d\'affichage de la section doit être un nombre entier.',
            'sections.*.ordre_affichage.min' => 'L\'ordre d\'affichage de la section doit être au moins 1.',
            'sections.*.type.string' => 'Le type de section doit être une chaîne de caractères.',
            'sections.*.type.max' => 'Le type de section ne peut pas dépasser 100 caractères.',

            // Messages pour les champs
            'champs.array' => 'Les champs doivent être un tableau.',
            'champs.*.label.required' => 'Le libellé du champ est obligatoire.',
            'champs.*.label.string' => 'Le libellé du champ doit être une chaîne de caractères.',
            'champs.*.label.max' => 'Le libellé du champ ne peut pas dépasser 255 caractères.',
            'champs.*.info.string' => 'L\'information du champ doit être une chaîne de caractères.',
            'champs.*.info.max' => 'L\'information du champ ne peut pas dépasser 65535 caractères.',
            'champs.*.attribut.string' => 'L\'attribut du champ doit être une chaîne de caractères.',
            'champs.*.attribut.max' => 'L\'attribut du champ ne peut pas dépasser 255 caractères.',
            'champs.*.placeholder.string' => 'Le placeholder du champ doit être une chaîne de caractères.',
            'champs.*.placeholder.max' => 'Le placeholder du champ ne peut pas dépasser 255 caractères.',
            'champs.*.is_required.boolean' => 'Le champ obligatoire doit être vrai ou faux.',
            'champs.*.default_value.string' => 'La valeur par défaut du champ doit être une chaîne de caractères.',
            'champs.*.default_value.max' => 'La valeur par défaut du champ ne peut pas dépasser 65535 caractères.',
            'champs.*.isEvaluated.boolean' => 'Le champ évalué doit être vrai ou faux.',
            'champs.*.commentaire.string' => 'Le commentaire du champ doit être une chaîne de caractères.',
            'champs.*.commentaire.max' => 'Le commentaire du champ ne peut pas dépasser 65535 caractères.',
            'champs.*.ordre_affichage.required' => 'L\'ordre d\'affichage du champ est obligatoire.',
            'champs.*.ordre_affichage.integer' => 'L\'ordre d\'affichage du champ doit être un nombre entier.',
            'champs.*.ordre_affichage.min' => 'L\'ordre d\'affichage du champ doit être au moins 1.',
            'champs.*.type_champ.required' => 'Le type de champ est obligatoire.',
            'champs.*.type_champ.string' => 'Le type de champ doit être une chaîne de caractères.',
            'champs.*.type_champ.in' => 'Le type de champ doit être: text, textarea, select, checkbox, radio, date, number, email ou file.',
            'champs.*.sectionId.integer' => 'L\'identifiant de la section doit être un nombre entier.',
            'champs.*.meta_options.array' => 'Les options méta du champ doivent être un tableau.',
            'champs.*.champ_config.array' => 'La configuration du champ doit être un tableau.',
            'champs.*.valeur_config.array' => 'La configuration de valeur du champ doit être un tableau.'
        ];
    }
}