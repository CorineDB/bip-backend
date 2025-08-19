<?php

namespace App\Http\Requests\evaluation;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmerResultatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        return auth()->check() && in_array(auth()->user()->type, ['dpaf', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'commentaire_confirmation' => 'required|string|min:20|max:2000',
            'confirmer' => 'required|boolean:true',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'commentaire_confirmation.required' => 'Le commentaire de confirmation est obligatoire.',
            'commentaire_confirmation.string' => 'Le commentaire de confirmation doit être du texte.',
            'commentaire_confirmation.min' => 'Le commentaire de confirmation doit contenir au moins 20 caractères.',
            'commentaire_confirmation.max' => 'Le commentaire de confirmation ne peut dépasser 2000 caractères.',
            'confirmer.required' => 'La confirmation est obligatoire.',
            'confirmer.boolean' => 'La confirmation doit être vraie ou fausse.',
            'confirmer.in' => 'Vous devez confirmer le résultat pour continuer.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'commentaire_confirmation' => 'commentaire de confirmation',
            'confirmer' => 'confirmation',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Vérifier que l'évaluation existe et peut être confirmée
            $evaluationId = $this->route('evaluationId') ?? $this->route('id');

            if ($evaluationId) {
                try {
                    $evaluation = app(\App\Repositories\Contracts\EvaluationRepositoryInterface::class)->find($evaluationId);

                    if (!$evaluation) {
                        $validator->errors()->add('evaluation', 'Évaluation non trouvée.');
                        return;
                    }

                    if ($evaluation->statut != 1) {
                        $validator->errors()->add('evaluation', 'L\'évaluation doit être terminée avant de pouvoir confirmer le résultat.');
                        return;
                    }

                    // Vérifier si le résultat n'a pas déjà été confirmé
                    if ($evaluation->valider_par && $evaluation->valider_le) {
                        $validator->errors()->add('evaluation', 'Le résultat de cette évaluation a déjà été confirmé.');
                        return;
                    }

                } catch (\Exception $e) {
                    $validator->errors()->add('evaluation', 'Erreur lors de la vérification de l\'évaluation.');
                }
            }
        });
    }
}