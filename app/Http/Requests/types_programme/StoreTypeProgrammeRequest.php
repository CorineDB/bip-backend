<?php

namespace App\Http\Requests\types_programme;

use App\Models\Dgpd;
use App\Models\TypeProgramme;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTypeProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd']) || (in_array($user->profilable_type, [Dgpd::class]) && ($user->hasPermissionTo('creer-un-programme') || $user->hasPermissionTo('gerer-un-programme')) ));
    }

    public function rules(): array
    {
        return [
            'type_programme'=> [
                'required',
                'string',
                Rule::unique('types_programme', 'type_programme')->whereNull('deleted_at')
            ],
            'type' => [
                'required',
                'string',
                'in:programme,composant-programme'
            ],
            'typeId' => [
                'required_if:type,composant-programme',
                new HashedExists(TypeProgramme::class)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type_programme.required' => 'Le type de programme est obligatoire.',
            'type_programme.string' => 'Le type de programme doit être une chaîne de caractères.',
            'type_programme.unique' => 'Ce type de programme existe déjà.',
            'type.required' => 'Le champ type est obligatoire.',
            'type.in' => 'Le champ type doit être soit "programme" soit "composant-programme".',
            'typeId.required_if' => 'L\'ID du type parent est obligatoire quand le type est "composant-programme".',
            'typeId.integer' => 'L\'ID du type parent doit être un nombre entier.',
            'typeId.exists' => 'Le type de programme parent sélectionné n\'existe pas.',
            'typeId.different' => 'Un type de programme ne peut pas être son propre parent.'
        ];
    }
}
