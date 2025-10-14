<?php

namespace App\Http\Requests\notes_conceptuelle;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Rapport;
use Illuminate\Validation\Rule;

class SoumettreRapportFaisabilitePreliminaireRequest extends FormRequest
{
    const DOCUMENT_RULE = 'distinct|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $estSoumise = $this->input('est_soumise', true);
        $rapportId = $this->input('rapportId');

        return [
            'est_soumise' => 'required|boolean',
            'rapportId' => ['sometimes', Rule::exists('rapports', 'id')->whereNull('deleted_at')],
            'intitule' => $estSoumise ? 'required|string|max:500' : 'nullable|string|max:500',

            // Documents
            'documents' => $estSoumise ? 'required|array' : 'nullable|array',
            'documents.rapport_faisabilite_preliminaire' => $estSoumise ? 'required|' . self::DOCUMENT_RULE : 'nullable|' . self::DOCUMENT_RULE,
            'documents.tdr_faisabilite_preliminaire' => $estSoumise ? 'required|' . self::DOCUMENT_RULE : 'nullable|' . self::DOCUMENT_RULE,
            'documents.check_suivi_rapport' => $estSoumise ? 'required|' . self::DOCUMENT_RULE : 'nullable|' . self::DOCUMENT_RULE,
            'documents.autres.*' => 'nullable|' . self::DOCUMENT_RULE,
/*
            // Analyse financière pour calcul VAN et TRI
            'analyse_financiere' => $estSoumise ? 'required|array' : 'nullable|array',
            'analyse_financiere.duree_vie' => $estSoumise ? 'required|integer|min:1' : 'nullable|integer|min:1',
            'analyse_financiere.investissement_initial' => $estSoumise ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'analyse_financiere.flux_tresorerie' => $estSoumise ? 'required|array' : 'nullable|array',
            'analyse_financiere.flux_tresorerie.*' => 'numeric',
            'analyse_financiere.taux_actualisation' => $estSoumise ? 'required|numeric|min:0|max:100' : 'nullable|numeric|min:0|max:100',

 */

            // Analyse financière requise seulement si le projet est MOU ET soumis
            'analyse_financiere'                            => ($estSoumise) ? 'required|array' : 'nullable|array|min:0',
            'analyse_financiere.duree_vie'                  => ($estSoumise) ? 'required|numeric' : 'nullable|numeric',
            'analyse_financiere.taux_actualisation'         => ($estSoumise) ? 'required|numeric' : 'nullable|numeric',
            'analyse_financiere.investissement_initial'     => ($estSoumise) ? 'required|numeric' : 'nullable|numeric',
            'analyse_financiere.flux_tresorerie'            => ($estSoumise) ? 'required|array|min:' . ($this->input("analyse_financiere.duree_vie") ?? 1) : 'nullable|array',
            'analyse_financiere.flux_tresorerie.*.t'        => ($estSoumise) ? 'required|numeric|min:1|max:' . ($this->input("analyse_financiere.duree_vie") ?? 1) : 'nullable|numeric',
            'analyse_financiere.flux_tresorerie.*.CFt'      => ($estSoumise) ? 'required|numeric|min:0' : 'nullable|numeric'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'est_soumise.required' => 'Le champ est_soumise est obligatoire.',
            'est_soumise.boolean' => 'Le champ est_soumise doit être un booléen.',
            'intitule.required' => 'L\'intitulé du rapport est obligatoire.',
            'intitule.string' => 'L\'intitulé doit être une chaîne de caractères.',
            'intitule.max' => 'L\'intitulé ne peut pas dépasser 500 caractères.',
            'documents.required' => 'Les documents sont obligatoires pour la soumission.',
            'documents.array' => 'Les documents doivent être un tableau.',
            'documents.rapport_faisabilite_preliminaire.required' => 'Le rapport de faisabilité préliminaire est obligatoire.',
            'documents.tdr_faisabilite_preliminaire.required' => 'Le TDR de faisabilité préliminaire est obligatoire.',
            'documents.check_suivi_rapport.required' => 'Le checklist de suivi du rapport est obligatoire.',
            'analyse_financiere.required' => 'L\'analyse financière est obligatoire pour la soumission.',
            'analyse_financiere.array' => 'L\'analyse financière doit être un tableau.',
            'analyse_financiere.duree_vie.required' => 'La durée de vie du projet est obligatoire.',
            'analyse_financiere.duree_vie.integer' => 'La durée de vie doit être un nombre entier.',
            'analyse_financiere.duree_vie.min' => 'La durée de vie doit être au moins 1 an.',
            'analyse_financiere.investissement_initial.required' => 'L\'investissement initial est obligatoire.',
            'analyse_financiere.investissement_initial.numeric' => 'L\'investissement initial doit être un nombre.',
            'analyse_financiere.investissement_initial.min' => 'L\'investissement initial doit être supérieur ou égal à 0.',
            'analyse_financiere.flux_tresorerie.required' => 'Les flux de trésorerie sont obligatoires.',
            'analyse_financiere.flux_tresorerie.array' => 'Les flux de trésorerie doivent être un tableau.',
            'analyse_financiere.flux_tresorerie.*.numeric' => 'Chaque flux de trésorerie doit être un nombre.',
            'analyse_financiere.taux_actualisation.required' => 'Le taux d\'actualisation est obligatoire.',
            'analyse_financiere.taux_actualisation.numeric' => 'Le taux d\'actualisation doit être un nombre.',
            'analyse_financiere.taux_actualisation.min' => 'Le taux d\'actualisation doit être supérieur ou égal à 0.',
            'analyse_financiere.taux_actualisation.max' => 'Le taux d\'actualisation ne peut pas dépasser 100.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'est_soumise' => 'statut de soumission',
            'intitule' => 'intitulé du rapport',
            'documents' => 'documents',
            'documents.rapport_faisabilite_preliminaire' => 'rapport de faisabilité préliminaire',
            'documents.tdr_faisabilite_preliminaire' => 'TDR de faisabilité préliminaire',
            'documents.check_suivi_rapport' => 'checklist de suivi',
            'analyse_financiere' => 'analyse financière',
            'analyse_financiere.duree_vie' => 'durée de vie',
            'analyse_financiere.investissement_initial' => 'investissement initial',
            'analyse_financiere.flux_tresorerie' => 'flux de trésorerie',
            'analyse_financiere.taux_actualisation' => 'taux d\'actualisation',
        ];
    }
}
