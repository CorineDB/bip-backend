<?php

namespace App\Http\Requests\financements;

use App\Enums\EnumTypeFinancement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $financementId = $this->route('financement') ? (is_string($this->route('financement')) ? $this->route('financement') : ($this->route('financement')->id)) : $this->route('id');

        return [
            'nom' => 'required|string|unique:financements,nom,' . $financementId,
            'nom_usuel' => 'required|string',
            'type' => ['required', Rule::in(EnumTypeFinancement::values())],
            'financementId' => 'sometimes|integer|exists:financements,id|different:' . $financementId
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du financement est obligatoire.',
            'nom.string' => 'Le nom du financement doit être une chaîne de caractères.',
            'nom.unique' => 'Ce nom de financement existe déjà.',
            'nom_usuel.required' => 'Le nom usuel du financement est obligatoire.',
            'nom_usuel.string' => 'Le nom usuel du financement doit être une chaîne de caractères.',
            'slug.required' => 'Le slug du financement est obligatoire.',
            'slug.string' => 'Le slug du financement doit être une chaîne de caractères.',
            'slug.max' => 'Le slug du financement ne peut pas dépasser 255 caractères.',
            'slug.unique' => 'Ce slug de financement existe déjà.',
            'slug.regex' => 'Le slug ne peut contenir que des lettres minuscules, chiffres, tirets et underscores.',
            'type.required' => 'Le type de financement est obligatoire.',
            'type.in' => 'Le type de financement sélectionné n\'est pas valide. Les valeurs autorisées sont : ' . implode(', ', EnumTypeFinancement::values()),
            'financementId.required' => 'L\'ID du financement parent est obligatoire.',
            'financementId.integer' => 'L\'ID du financement parent doit être un nombre entier.',
            'financementId.exists' => 'Le financement parent sélectionné n\'existe pas.',
            'financementId.different' => 'Un financement ne peut pas être son propre parent.'
        ];
    }
}