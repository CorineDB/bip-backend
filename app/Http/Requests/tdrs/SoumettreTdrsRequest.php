<?php

namespace App\Http\Requests\tdrs;

use Illuminate\Foundation\Http\FormRequest;

class SoumettreTdrsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->type, ['dpaf', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'fichier_tdr' => 'required|file|mimes:pdf,doc,docx|max:10240', // Max 10MB
            'resume' => 'required|string|min:50|max:2000',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'fichier_tdr.required' => 'Le fichier TDR est obligatoire.',
            'fichier_tdr.file' => 'Le TDR doit être un fichier.',
            'fichier_tdr.mimes' => 'Le TDR doit être un fichier PDF, DOC ou DOCX.',
            'fichier_tdr.max' => 'Le fichier TDR ne peut dépasser 10 MB.',
            'resume.required' => 'Un résumé est obligatoire.',
            'resume.string' => 'Le résumé doit être du texte.',
            'resume.min' => 'Le résumé doit contenir au moins 50 caractères.',
            'resume.max' => 'Le résumé ne peut dépasser 2000 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'fichier_tdr' => 'fichier TDR',
            'resume' => 'résumé',
        ];
    }
}