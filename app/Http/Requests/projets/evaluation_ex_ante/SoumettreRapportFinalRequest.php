<?php

namespace App\Http\Requests\projets\evaluation_ex_ante;

use App\Models\Dpaf;
use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;

class SoumettreRapportFinalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (($user->hasPermissionTo('soumettre-un-rapport-d-evaluation-ex-ante')) && in_array($user->profilable_type, [Dpaf::class, Organisation::class]) && $user->profilable->ministere);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rapport_evaluation_ex_ante' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480', // Max 20MB
            'documents_annexe' => 'array|min:0',
            'documents_annexe.*' => 'distinct|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,txt|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/png,image/jpeg,text/plain|max:20480', // Max 20MB
            'commentaire' => 'required|string|max:500'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'rapport_faisabilite.required' => 'Le rapport de faisabilité est obligatoire.',
            'rapport_faisabilite.file' => 'Le rapport de faisabilité doit être un fichier.',
            'rapport_faisabilite.mimes' => 'Le rapport de faisabilité doit être un fichier PDF, DOC ou DOCX.',
            'rapport_faisabilite.max' => 'Le rapport de faisabilité ne peut dépasser 20 MB.',
            'rapport_couts_avantages.required' => 'Le rapport des coûts et avantages sociaux est obligatoire.',
            'rapport_couts_avantages.file' => 'Le rapport des coûts et avantages doit être un fichier.',
            'rapport_couts_avantages.mimes' => 'Le rapport des coûts et avantages doit être un fichier PDF, DOC, DOCX, XLS ou XLSX.',
            'rapport_couts_avantages.max' => 'Le rapport des coûts et avantages ne peut dépasser 15 MB.',
            'nom_cabinet.required' => 'Le nom du cabinet est obligatoire.',
            'nom_cabinet.string' => 'Le nom du cabinet doit être du texte.',
            'nom_cabinet.max' => 'Le nom du cabinet ne peut dépasser 255 caractères.',
            'contact_cabinet.string' => 'Le contact du cabinet doit être du texte.',
            'contact_cabinet.max' => 'Le contact du cabinet ne peut dépasser 255 caractères.',
            'email_cabinet.email' => 'L\'email du cabinet doit être valide.',
            'email_cabinet.max' => 'L\'email du cabinet ne peut dépasser 255 caractères.',
            'telephone_cabinet.string' => 'Le téléphone du cabinet doit être du texte.',
            'telephone_cabinet.max' => 'Le téléphone du cabinet ne peut dépasser 20 caractères.',
            'adresse_cabinet.string' => 'L\'adresse du cabinet doit être du texte.',
            'adresse_cabinet.max' => 'L\'adresse du cabinet ne peut dépasser 500 caractères.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'rapport_faisabilite' => 'rapport de faisabilité',
            'rapport_couts_avantages' => 'rapport des coûts et avantages sociaux',
            'nom_cabinet' => 'nom du cabinet',
            'contact_cabinet' => 'contact du cabinet',
            'email_cabinet' => 'email du cabinet',
            'telephone_cabinet' => 'téléphone du cabinet',
            'adresse_cabinet' => 'adresse du cabinet'
        ];
    }
}
