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
        ];
    }
}
