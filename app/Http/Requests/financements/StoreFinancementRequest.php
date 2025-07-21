<?php

namespace App\Http\Requests\financements;

use App\Enums\EnumTypeFinancement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'=> ['required', 'string', Rule::unique('financements', 'nom')->whereNull('deleted_at')],
            'nom_usuel' => 'required|string',
            'type' => ['required', Rule::in(EnumTypeFinancement::values())],
            'financementId' => ['required', Rule::exists('financements', 'id')->whereNull('deleted_at')]
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
            'type.required' => 'Le type de financement est obligatoire.',
            'type.in' => 'Le type de financement sélectionné n\'est pas valide. Les valeurs autorisées sont : ' . implode(', ', EnumTypeFinancement::values()),
            'financementId.required' => 'L\'ID du financement parent est obligatoire.',
            'financementId.integer' => 'L\'ID du financement parent doit être un nombre entier.',
            'financementId.exists' => 'Le financement parent sélectionné n\'existe pas.',
            'financementId.different' => 'Un financement ne peut pas être son propre parent.'
        ];
    }
}