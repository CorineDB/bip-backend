<?php

namespace App\Http\Requests\documents\fiches_idee;

use App\Enums\EnumTypeChamp;
use App\Models\Champ;
use App\Models\ChampSection;
use App\Models\Document;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrUpdateFicheIdeeRequest extends FormRequest
{
    private $fiche = null;
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->type, ['super-admin', 'dgpd']);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->has('sections') && !$this->has('champs')) {
                $validator->errors()->add('sections', 'Au moins une section ou un champ doit être fourni.');
            }

            // Validation des attributs des champs
            $this->validateChampsAttributs($validator);
        });
    }

    public function prepareForValidation(){

        $this->fiche = Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'fiche-idee');
        })
        ->where('type', 'formulaire')
        ->orderBy('created_at', 'desc')
        ->first();
    }

    /**
     * Valide que les attributs des champs existent dans la liste autorisée
     */
    private function validateChampsAttributs($validator)
    {
        $requiredAttributes = $this->getValidAttributs();

        $foundAttributs = [];

        // Champs racines
        foreach ($this->input('champs', []) as $index => $champ) {
            if (isset($champ['attribut'])) {
                $foundAttributs[] = $champ['attribut'];
            }

            // Valider l'ID hashé du champ
            if (isset($champ['id'])) {
                $idValidator = new HashedExists(Champ::class);
                if (!$idValidator->passes("champs.{$index}.id", $champ['id'])) {
                    $validator->errors()->add("champs.{$index}.id", $idValidator->message());
                }
            }
        }

        // Champs dans sections (récursif pour gérer les sous-sections)
        foreach ($this->input('sections', []) as $index => $section) {
            // Valider l'ID hashé de la section
            if (isset($section['id'])) {
                $idValidator = new HashedExists(ChampSection::class);
                if (!$idValidator->passes("sections.{$index}.id", $section['id'])) {
                    $validator->errors()->add("sections.{$index}.id", $idValidator->message());
                }
            }

            $this->extractAttributsFromSection($section, $foundAttributs, $validator, "sections.{$index}");
        }

        $foundAttributs = array_unique($foundAttributs);

        // Vérifie que tous les attributs requis sont présents
        foreach ($requiredAttributes as $required) {
            if (!in_array($required, $foundAttributs)) {
                $validator->errors()->add('attributs_manquants', "Le champ avec l'attribut '$required' est obligatoire.");
            }
        }
    }

    /**
     * Extrait récursivement les attributs des champs d'une section et de ses sous-sections
     */
    private function extractAttributsFromSection(array $section, array &$foundAttributs, $validator, string $path): void
    {
        // Champs directs de la section
        foreach ($section['champs'] ?? [] as $index => $champ) {
            if (isset($champ['attribut'])) {
                $foundAttributs[] = $champ['attribut'];
            }

            // Valider l'ID hashé du champ
            if (isset($champ['id'])) {
                $idValidator = new HashedExists(Champ::class);
                if (!$idValidator->passes("{$path}.champs.{$index}.id", $champ['id'])) {
                    $validator->errors()->add("{$path}.champs.{$index}.id", $idValidator->message());
                }
            }
        }

        // Champs dans les sous-sections (récursif)
        if (isset($section['sous_sections']) && is_array($section['sous_sections'])) {
            foreach ($section['sous_sections'] as $index => $sousSection) {
                // Valider l'ID hashé de la sous-section
                if (isset($sousSection['id'])) {
                    $idValidator = new HashedExists(ChampSection::class);
                    if (!$idValidator->passes("{$path}.sous_sections.{$index}.id", $sousSection['id'])) {
                        $validator->errors()->add("{$path}.sous_sections.{$index}.id", $idValidator->message());
                    }
                }

                $this->extractAttributsFromSection($sousSection, $foundAttributs, $validator, "{$path}.sous_sections.{$index}");
            }
        }
    }

    /**
     * Liste des attributs valides pour les fiches idées
     */
    private function getValidAttributs(): array
    {
        return [
            "cout_estimatif_projet",
            "sigle",
            "parties_prenantes",
            "objectifs_specifiques",
            "resultats_attendus",
            "cout_dollar_americain",
            "cout_euro",
            "cout_dollar_canadien",
            "risques_immediats",
            "sommaire",
            //"objectif_general",
            "conclusions",
            "constats_majeurs",
            "description",
            "public_cible",
            "estimation_couts",
            "impact_environnement",
            "aspect_organisationnel",
            "description_extrants",
            "duree",
            "description_projet",
            "origine",
            "situation_desiree",
            "situation_actuelle",
            "contraintes",
            "echeancier",
            //"fondement",
            "secteurId",
            "categorieId",
            /*"ministereId",
            "responsableId",
            /*"demandeurId",*/
            //"demandeur",
            "titre_projet",
            "departements",
            "communes",
            "arrondissements",
            "villages",
            "cibles",
            "odds",
            "types_financement",
            "natures_financement",
            "sources_financement",
            "orientations_strategiques",
            "objectifs_strategiques",
            "resultats_strategiques",
            "axes_pag",
            "actions_pag",
            "piliers_pag",
        ];
    }

    public function rules(): array
    {
        return [
            // Document fields
            'nom' => [
                'required', 'bail',
                function ($attribute, $value, $fail) {
                    $exists = Document::where('nom', $value)
                        ->whereHas('categorie', function ($query) {
                            $query->where('slug', 'fiche-idee');
                        })->when($this->fiche, function($query){
                            $query->where("id","<>", $this->fiche->id);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The nom has already been taken for documents in this category.');
                    }
                }
            ],
            'description' => 'nullable|string|max:65535',
            'type' => ['required', 'string', Rule::in(['document', 'formulaire', 'grille', 'checklist'])],

            // Sections
            'sections' => 'sometimes|array|min:1',
            'sections.*.id' => ["sometimes"/* Rule::requiredIf($this->fiche) */, Rule::exists('champs_sections', 'id')->whereNull('deleted_at')],
            'sections.*.intitule' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.ordre_affichage' => 'required|integer|min:1',
            'sections.*.type' => 'nullable|string|max:100',

            // Champs dans les sections
            'sections.*.champs' => 'sometimes|array|min:1',
            'sections.*.champs.*.id' => ["sometimes"/* Rule::requiredIf($this->fiche) */, Rule::exists('champs', 'id')->whereNull('deleted_at')],
            'sections.*.champs.*.label' => 'required|string|max:255',
            'sections.*.champs.*.info' => 'nullable|string|max:65535',
            'sections.*.champs.*.attribut' => 'sometimes|nullable|string|max:255',
            'sections.*.champs.*.placeholder' => 'nullable|string|max:255',
            'sections.*.champs.*.is_required' => 'boolean',
            'sections.*.champs.*.champ_standard' => 'boolean',
            'sections.*.champs.*.default_value' => 'nullable|max:65535',
            'sections.*.champs.*.isEvaluated' => 'boolean',
            'sections.*.champs.*.ordre_affichage' => 'required|integer|min:1',
            'sections.*.champs.*.type_champ' => [
                'required',
                'string',
                Rule::in(EnumTypeChamp::values())
                //Rule::in(['text', 'textarea', 'select', 'checkbox', 'radio', 'date', 'number', 'email', 'file'])
            ],
            'sections.*.champs.*.meta_options' => 'required|array',
            'sections.*.champs.*.meta_options.conditions' => 'required|array|min:3',
            'sections.*.champs.*.meta_options.conditions.visible' => 'required|boolean',
            'sections.*.champs.*.meta_options.conditions.disable' => 'required|boolean',
            'sections.*.champs.*.meta_options.conditions.conditions' => 'nullable|array|min:0',
            'sections.*.champs.*.meta_options.validations_rules' => 'required|array|min:1',
            'sections.*.champs.*.meta_options.validations_rules.required' => 'required|boolean',
            'sections.*.champs.*.meta_options.configs' => 'required|array|min:0',

            // Champs racine (sans section)
            'champs' => 'sometimes|array|min:1',
            'champs.*.id' => ["sometimes"/* Rule::requiredIf($this->fiche) */, Rule::exists('champs', 'id')->whereNull('deleted_at')],
            'champs.*.label' => 'required|string|max:255',
            'champs.*.info' => 'nullable|string|max:65535',
            'champs.*.attribut' => 'sometimes|nullable|string|max:255',
            'champs.*.placeholder' => 'nullable|string|max:255',
            'champs.*.is_required' => 'boolean',
            'champs.*.champ_standard' => 'boolean',
            'champs.*.default_value' => 'nullable|max:65535',
            'champs.*.isEvaluated' => 'boolean',
            'champs.*.ordre_affichage' => 'required|integer|min:1',
            'champs.*.type_champ' => [
                'required',
                'string',
                Rule::in(EnumTypeChamp::values())
            ],
            'champs.*.meta_options' => 'required|array',
            'champs.*.meta_options.conditions' => 'required|array|min:3',
            'champs.*.meta_options.conditions.visible' => 'required|boolean',
            'champs.*.meta_options.conditions.disable' => 'required|boolean',
            'champs.*.meta_options.conditions.conditions' => 'nullable|array|min:0',
            'champs.*.meta_options.validations_rules' => 'required|array|min:1',
            'champs.*.meta_options.validations_rules.required' => 'required|boolean',
            'champs.*.meta_options.configs' => 'required|array|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            // Messages pour le document
            'nom.required' => 'Le nom du document est obligatoire.',
            'nom.string' => 'Le nom du document doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du document ne peut pas dépasser 65535 caractères.',
            'nom.unique' => 'Ce document existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 65535 caractères.',
            'type.required' => 'Le type de document est obligatoire.',
            'type.string' => 'Le type de document doit être une chaîne de caractères.',
            'type.in' => 'Le type de document doit être: document, formulaire, grille ou checklist.',

            // Messages pour les sections
            'sections.array' => 'Les sections doivent être un tableau.',
            'sections.*.id.integer' => 'L\'ID de la section doit être un nombre entier.',
            'sections.*.id.exists' => 'La section sélectionnée n\'existe pas.',
            'sections.*.intitule.required' => 'L\'intitulé de la section est obligatoire.',
            'sections.*.intitule.string' => 'L\'intitulé de la section doit être une chaîne de caractères.',
            'sections.*.intitule.max' => 'L\'intitulé de la section ne peut pas dépasser 255 caractères.',
            'sections.*.ordre_affichage.required' => 'L\'ordre d\'affichage de la section est obligatoire.',
            'sections.*.ordre_affichage.integer' => 'L\'ordre d\'affichage de la section doit être un nombre entier.',
            'sections.*.ordre_affichage.min' => 'L\'ordre d\'affichage de la section doit être au moins 1.',
            'sections.*.type.string' => 'Le type de section doit être une chaîne de caractères.',
            'sections.*.type.max' => 'Le type de section ne peut pas dépasser 100 caractères.',

            // Messages pour les champs dans sections
            'sections.*.champs.array' => 'Les champs de la section doivent être un tableau.',
            'sections.*.champs.*.id.integer' => 'L\'ID du champ doit être un nombre entier.',
            'sections.*.champs.*.id.exists' => 'Le champ sélectionné n\'existe pas.',
            'sections.*.champs.*.label.required' => 'Le libellé du champ est obligatoire.',
            'sections.*.champs.*.label.string' => 'Le libellé du champ doit être une chaîne de caractères.',
            'sections.*.champs.*.label.max' => 'Le libellé du champ ne peut pas dépasser 255 caractères.',
            'sections.*.champs.*.attribut.required' => 'L\'attribut du champ est obligatoire.',
            'sections.*.champs.*.attribut.string' => 'L\'attribut du champ doit être une chaîne de caractères.',
            'sections.*.champs.*.attribut.max' => 'L\'attribut du champ ne peut pas dépasser 255 caractères.',
            'sections.*.champs.*.type_champ.required' => 'Le type de champ est obligatoire.',
            'sections.*.champs.*.type_champ.in' => 'Le type de champ doit être: text, textarea, select, checkbox, radio, date, number, email ou file.',

            // Messages pour les champs racine
            'champs.array' => 'Les champs doivent être un tableau.',
            'champs.*.id.integer' => 'L\'ID du champ doit être un nombre entier.',
            'champs.*.id.exists' => 'Le champ sélectionné n\'existe pas.',
            'champs.*.label.required' => 'Le libellé du champ est obligatoire.',
            'champs.*.label.string' => 'Le libellé du champ doit être une chaîne de caractères.',
            'champs.*.label.max' => 'Le libellé du champ ne peut pas dépasser 255 caractères.',
            'champs.*.attribut.required' => 'L\'attribut du champ est obligatoire.',
            'champs.*.attribut.string' => 'L\'attribut du champ doit être une chaîne de caractères.',
            'champs.*.attribut.max' => 'L\'attribut du champ ne peut pas dépasser 255 caractères.',
            'champs.*.type_champ.required' => 'Le type de champ est obligatoire.',
            'champs.*.type_champ.in' => 'Le type de champ doit être: text, textarea, select, checkbox, radio, date, number, email ou file.',
        ];
    }
}
