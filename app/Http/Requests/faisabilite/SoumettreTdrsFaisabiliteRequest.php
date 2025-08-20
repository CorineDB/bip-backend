<?php

namespace App\Http\Requests\faisabilite;

use Illuminate\Foundation\Http\FormRequest;

class SoumettreTdrsFaisabiliteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //auth()->check() && in_array(auth()->user()->type, ['dpaf', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tdr' => 'required|file|mimes:pdf,doc,xls,xlsx,docx|max:10240', // Max 10MB
            'resume_tdr_faisabilite' => 'required|string|min:50|max:2000'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'tdr.required' => 'Le fichier TDR est obligatoire.',
            'tdr.file' => 'Le TDR doit être un fichier.',
            'tdr.mimes' => 'Le TDR doit être un fichier PDF, DOC, XLS, XLSX ou DOCX.',
            'tdr.max' => 'Le fichier TDR ne peut dépasser 10 MB.',
            'resume_tdr_faisabilite.required' => 'Un résumé est obligatoire.',
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
            'resume_tdr_faisabilite' => 'résumé',
        ];
    }
}