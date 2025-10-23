<?php

namespace App\Http\Requests\composants_programme;

use App\Models\Dgpd;
use App\Models\TypeProgramme;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComposantProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd']) || (in_array($user->profilable_type, [Dgpd::class]) && ($user->hasPermissionTo('modifier-un-composant-de-programme') || $user->hasPermissionTo('gerer-les-composants-de-programme')) ));
    }

    public function rules(): array
    {
        $composantProgrammeId = $this->route('composants_programme') ? $this->route('composants_programme')->id : $this->route('id');

        return [
            'indice' => 'required|integer|min:1',
            'intitule'=> ['required', 'string', Rule::unique('composants_programme', 'intitule')->ignore($composantProgrammeId)->whereNull('deleted_at')],

            'typeId' => ['required', new HashedExists(TypeProgramme::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code du composant programme est obligatoire.',
            'code.string' => 'Le code du composant programme doit être une chaîne de caractères.',
            'code.max' => 'Le code du composant programme ne peut pas dépasser 255 caractères.',
            'code.unique' => 'Ce code de composant programme existe déjà.',
            'indice.required' => 'L\'indice du composant programme est obligatoire.',
            'indice.integer' => 'L\'indice du composant programme doit être un nombre entier.',
            'indice.min' => 'L\'indice du composant programme doit être supérieur à 0.',
            'intitule.required' => 'L\'intitulé du composant programme est obligatoire.',
            'intitule.string' => 'L\'intitulé du composant programme doit être une chaîne de caractères.',
            'intitule.unique' => 'Cet intitulé de composant programme existe déjà.',
            'typeId.required' => 'Le type de programme est obligatoire.',
            'typeId.integer' => 'L\'ID du type de programme doit être un nombre entier.',
            'typeId.exists' => 'Le type de programme sélectionné n\'existe pas.'
        ];
    }
}
