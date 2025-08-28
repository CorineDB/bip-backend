<?php

namespace App\Http\Requests\validation;

use Illuminate\Foundation\Http\FormRequest;

class ValiderEtudeProfilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //auth()->check() && in_array(auth()->user()->type, ['comite_ministeriel', 'dpaf', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'decision'          => 'required|string|in:faire_etude_prefaisabilite,reviser_note_conceptuelle,abandonner_projet,sauvegarder',
            'commentaire'       => 'required|string|min:10|max:2000',
            'est_haut_risque'   => 'required|boolean:false',
            'est_dur'           => 'required|boolean:false',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Une décision est obligatoire.',
            'decision.string' => 'La décision doit être du texte.',
            'decision.in' => 'La décision doit être l\'une des valeurs suivantes : Projet à maturité, Faire une étude de pré-faisabilité, Réviser la note conceptuelle, Abandonner le projet, Sauvegarder.',
            'commentaire.required' => 'Un commentaire est obligatoire.',
            'commentaire.string' => 'Le commentaire doit être du texte.',
            'commentaire.min' => 'Le commentaire doit contenir au moins 10 caractères.',
            'commentaire.max' => 'Le commentaire ne peut dépasser 2000 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'decision' => 'décision',
            'commentaire' => 'commentaire',
            'est_haut_risque' => 'est un projet a haut risque',
            'est_dur' => 'est un projet de nature dur',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Vérifier que le projet existe et est au bon statut
            $projetId = $this->route('projetId') ?? $this->route('id');

            if ($projetId) {
                try {
                    $projet = app(\App\Repositories\Contracts\ProjetRepositoryInterface::class)->find($projetId);

                    if (!$projet) {
                        $validator->errors()->add('projet', 'Projet non trouvé.');
                        return;
                    }

                    if ($projet->statut->value !== \App\Enums\StatutIdee::VALIDATION_PROFIL->value) {
                        $validator->errors()->add('projet', 'Le projet n\'est pas à l\'étape de validation d\'étude de profil.');
                        return;
                    }

                } catch (\Exception $e) {
                    $validator->errors()->add('projet', 'Erreur lors de la vérification du projet.');
                }
            }
        });
    }

    /**
     * Get the validation messages with decision labels.
     */
    public function getDecisionLabels(): array
    {
        return [
            'projet_a_maturite' => 'Projet à maturité',
            'faire_etude_prefaisabilite' => 'Faire une étude de pré-faisabilité',
            'reviser_note_conceptuelle' => 'Réviser la note conceptuelle',
            'abandonner_projet' => 'Abandonner le projet',
            'sauvegarder' => 'Sauvegarder'
        ];
    }
}