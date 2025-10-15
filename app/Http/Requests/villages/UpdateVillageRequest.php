<?php

namespace App\Http\Requests\villages;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashedExists;
use App\Models\Village;
use App\Models\Arrondissement;

class UpdateVillageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adapter selon vos besoins d'autorisation
    }

    /**
     * Préparer les données pour la validation
     */
    protected function prepareForValidation(): void
    {
        // Déhasher l'arrondissementId si fourni en tant que hashed_id
        if ($this->has('arrondissement_hashed_id')) {
            $unhashedId = Arrondissement::unhashId($this->arrondissement_hashed_id);
            $this->merge([
                'arrondissementId' => $unhashedId
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:villages,code,' . $this->route('id')],
            'slug' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],

            // Exemple 1: Utilisation avec HashedExists et nom de classe
            'arrondissement_hashed_id' => [
                'sometimes',
                new HashedExists(Arrondissement::class)
            ],

            // Exemple 2: Utilisation directe avec arrondissementId (déjà déhashé dans prepareForValidation)
            'arrondissementId' => [
                'sometimes',
                'exists:arrondissements,id'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du village est requis.',
            'nom.string' => 'Le nom du village doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du village ne peut pas dépasser 255 caractères.',

            'code.unique' => 'Ce code est déjà utilisé par un autre village.',

            'arrondissement_hashed_id.required' => 'L\'arrondissement est requis.',

            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom du village',
            'code' => 'code du village',
            'arrondissement_hashed_id' => 'arrondissement',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ];
    }
}
