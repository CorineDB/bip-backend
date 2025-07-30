<?php

namespace App\Http\Requests\secteurs;

use App\Enums\EnumTypeSecteur;
use App\Models\Secteur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSecteurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'=> ['required', 'string', Rule::unique('secteurs', 'nom')->whereNull('deleted_at')],
            'description' => 'nullable|string|max:65535',
            'type' => ['required', 'string', Rule::in(EnumTypeSecteur::values())],
            'secteurId' => [Rule::requiredIf($this->input("type") != "grand-secteur"),

                function ($attribute, $value, $fail) {
                    $exists = Secteur::where("secteurId", $value)->when($this->input("type") == "secteur", function($query){

                        $query->whereHasNot('parent', function ($query) {
                            $query->where('type', 'grand-secteur');
                        });
                    })->when($this->input("type") == "sous-secteur", function($query){

                        $query->whereHas('parent', function ($query) {
                            $query->where('type', 'secteur');
                        });
                    })->whereNull('deleted_at')->exists();

                    if (!$exists && $this->input("type") == "secteur") {
                        $fail('Le grand secteur est inconnu');
                    }
                    else if (!$exists && $this->input("type") == "sous-secteur") {
                        $fail('Le Secteur est inconnu');
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du secteur est obligatoire.',
            'nom.string' => 'Le nom du secteur doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du secteur ne peut pas dépasser 65535 caractères.',
            'nom.unique' => 'Ce secteur existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 65535 caractères.',
            'type.required' => 'Le type de secteur est obligatoire.',
            'type.string' => 'Le type de secteur doit être une chaîne de caractères.',
            'type.in' => 'Le type de secteur doit être: ' . implode(', ', EnumTypeSecteur::values()),
            'secteurId.integer' => 'L\'identifiant du secteur parent doit être un nombre entier.',
            'secteurId.exists' => 'Le secteur parent sélectionné n\'existe pas.'
        ];
    }
}