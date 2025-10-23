<?php

namespace App\Http\Requests\projets\evaluation_ex_ante;

use App\Models\Dgpd;
use Illuminate\Foundation\Http\FormRequest;

class ValiderRapportFinalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (($user->hasPermissionTo('valider-un-rapport-evaluation-ex-ante')) && in_array($user->profilable_type, [Dgpd::class]));
        return true; //auth()->check() && in_array(auth()->user()->type, ['comite_ministeriel', 'dgpd', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'action' => 'required|string|in:valider,corriger',
            'commentaire' => 'nullable|string|max:2000'
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
            'commentaire.max' => 'Le commentaire ne peut dépasser 2000 caractères.',
            'checklist_controle.array' => 'La checklist de contrôle doit être un tableau.',
            'checklist_controle.*.critere.required' => 'Le critère est obligatoire.',
            'checklist_controle.*.critere.string' => 'Le critère doit être du texte.',
            'checklist_controle.*.validation.required' => 'La validation du critère est obligatoire.',
            'checklist_controle.*.validation.boolean' => 'La validation doit être vraie ou fausse.',
            'checklist_controle.*.commentaire.string' => 'Le commentaire du critère doit être du texte.',
            'checklist_controle.*.commentaire.max' => 'Le commentaire du critère ne peut dépasser 500 caractères.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'action' => 'action de validation',
            'commentaire' => 'commentaire',
            'checklist_controle' => 'checklist de contrôle qualité'
        ];
    }

    /**
     * Get the validation actions with their descriptions
     */
    public function getActionDescriptions(): array
    {
        return [
            'maturite' => 'Projet à maturité - Le projet est validé et passe au statut maturité',
            'reprendre' => 'Reprendre l\'étude de faisabilité - Le projet retourne à la soumission du rapport',
            'abandonner' => 'Abandonner le projet - Le projet est définitivement abandonné'
        ];
    }
}
