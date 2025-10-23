<?php

namespace App\Http\Requests\faisabilite;

use App\Models\Dgpd;
use Illuminate\Foundation\Http\FormRequest;

class ValiderTdrsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (($user->hasPermissionTo('apprecier-un-tdr-de-faisabilite') || $user->hasPermissionTo('valider-un-tdr-de-faisabilite')) && in_array($user->profilable_type, [Dgpd::class]));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'action' => 'required|string|in:reviser,abandonner',
            'commentaire' => 'nullable|string|min:30|max:2000',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'L\'action de validation est obligatoire.',
            'action.in' => 'Action invalide. Actions possibles: reviser, abandonner.',
            'commentaire.string' => 'Le commentaire doit être du texte.',
            'commentaire.min' => 'Le commentaire doit etre au minimum 30 caractères.',
            'commentaire.max' => 'Le commentaire ne peut dépasser 2000 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'action' => 'action de validation',
            'commentaire' => 'commentaire global'
        ];
    }
}
