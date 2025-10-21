<?php

namespace App\Http\Requests\types_programme;

use App\Models\TypeProgramme;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTypeProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        // Récupère l'ID du type_programme en fonction de la route
        $typeProgrammeId = $this->route('type_programme')
            ? ((is_string($this->route('type_programme')) || is_numeric($this->route('type_programme')))
                ? $this->route('type_programme')
                : $this->route('type_programme')->id)
            : $this->route('id');

        return [
            'type_programme'=> [
                'required',
                'string',
                Rule::unique('types_programme', 'type_programme')
                    ->ignore($typeProgrammeId) // ignore l'ID actuel
                    ->whereNull('deleted_at')
            ],
            'type' => [
                'required',
                'string',
                'in:programme,composant-programme'
            ],
            'typeId' => [
                'required_if:type,composant-programme',
                new HashedExists(TypeProgramme::class),
                "different:$typeProgrammeId" // ne peut pas être son propre parent
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
            'type.string' => 'Le champ type doit être une chaîne de caractères.',
            'type.in' => 'Le champ type doit être soit "programme" soit "composant-programme".',
            'typeId.required_if' => 'L\'ID du type parent est obligatoire quand le type est "composant-programme".',
            'typeId.integer' => 'L\'ID du type parent doit être un nombre entier.',
            'typeId.exists' => 'Le type de programme parent sélectionné n\'existe pas.',
            'typeId.different' => 'Un type de programme ne peut pas être son propre parent.'
        ];
    }
}
