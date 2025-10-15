<?php

namespace App\Http\Requests\villages;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashedExists;
use App\Models\Arrondissement;

class StoreVillageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adapter selon vos besoins d'autorisation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:villages,code'],
            'slug' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // La règle HashedExists fait tout le travail : validation + déhashage + mise à jour
            'arrondissementId' => [
                'required',
                new HashedExists(Arrondissement::class)
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

            'code.required' => 'Le code du village est requis.',
            'code.unique' => 'Ce code est déjà utilisé par un autre village.',

            'arrondissementId.required' => 'L\'arrondissement est requis.',
            'arrondissementId.exists' => 'L\'arrondissement sélectionné n\'existe pas.',

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
            'arrondissementId' => 'arrondissement',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ];
    }
}
