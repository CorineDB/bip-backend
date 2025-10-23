<?php

namespace App\Http\Requests\faisabilite;

use App\Models\Dpaf;
use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;

class SoumettreTdrsFaisabiliteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (($user->hasPermissionTo('soumettre-tdr-faisabilite')) && in_array($user->profilable_type, [Dpaf::class, Organisation::class]) && $user->profilable->ministere);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $estSoumise = $this->input('est_soumise', true);

        return [
            'est_soumise' => 'required|boolean',
            'tdr' => 'nullable|file|mimes:pdf,doc,xls,xlsx,docx|max:10240', // Max 10MB
            'numero_dossier'                        => 'required_unless:est_soumise,0|sometimes|string|max:50',
            'numero_contrat'                        => 'required_unless:est_soumise,0|sometimes|string|max:50',
            'autres_document' => 'nullable|array',
            'autres_document.*' => 'file|mimes:pdf,doc,xls,xlsx,docx,jpg,jpeg,png|max:10240', // Max 10MB par fichier
            'resume_tdr_faisabilite' => $estSoumise ? 'required|string|min:50|max:2000' : 'nullable|string|max:2000'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'est_soumise.boolean' => 'Le champ est_soumise doit être vrai ou faux.',
            'tdr.file' => 'Le TDR doit être un fichier.',
            'tdr.mimes' => 'Le TDR doit être un fichier PDF, DOC, XLS, XLSX ou DOCX.',
            'tdr.max' => 'Le fichier TDR ne peut dépasser 10 MB.',
            'autres_document.array' => 'Les autres documents doivent être un tableau de fichiers.',
            'autres_document.*.file' => 'Chaque document doit être un fichier.',
            'autres_document.*.mimes' => 'Chaque document doit être un fichier PDF, DOC, XLS, XLSX, DOCX, JPG, JPEG ou PNG.',
            'autres_document.*.max' => 'Chaque fichier ne peut dépasser 10 MB.',
            'resume_tdr_faisabilite.required' => 'Un résumé est obligatoire pour soumettre.',
            'resume_tdr_faisabilite.string' => 'Le résumé doit être du texte.',
            'resume_tdr_faisabilite.min' => 'Le résumé doit contenir au moins 50 caractères.',
            'resume_tdr_faisabilite.max' => 'Le résumé ne peut dépasser 2000 caractères.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tdr' => 'fichier TDR',
            'autres_document' => 'autres documents',
            'resume_tdr_faisabilite' => 'résumé',
        ];
    }
}
