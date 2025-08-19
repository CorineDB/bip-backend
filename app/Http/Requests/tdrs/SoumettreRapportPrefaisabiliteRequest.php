<?php

namespace App\Http\Requests\tdrs;

use Illuminate\Foundation\Http\FormRequest;

class SoumettreRapportPrefaisabiliteRequest extends FormRequest
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
            'fichier_rapport' => 'required|file|mimes:pdf,doc,docx|max:20480', // Max 20MB
            'nom_cabinet' => 'required|string|max:255',
            'contact_cabinet' => 'required|string|max:255',
            'email_cabinet' => 'required|email|max:255',
            'telephone_cabinet' => 'nullable|string|max:20',
            'adresse_cabinet' => 'nullable|string|max:500',
            'recommandation_adaptation' => 'required|string|min:100|max:5000',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'fichier_rapport.required' => 'Le fichier rapport est obligatoire.',
            'fichier_rapport.file' => 'Le rapport doit être un fichier.',
            'fichier_rapport.mimes' => 'Le rapport doit être un fichier PDF, DOC ou DOCX.',
            'fichier_rapport.max' => 'Le fichier rapport ne peut dépasser 20 MB.',
            
            'nom_cabinet.required' => 'Le nom du cabinet est obligatoire.',
            'nom_cabinet.string' => 'Le nom du cabinet doit être du texte.',
            'nom_cabinet.max' => 'Le nom du cabinet ne peut dépasser 255 caractères.',
            
            'contact_cabinet.required' => 'Le contact du cabinet est obligatoire.',
            'contact_cabinet.string' => 'Le contact du cabinet doit être du texte.',
            'contact_cabinet.max' => 'Le contact du cabinet ne peut dépasser 255 caractères.',
            
            'email_cabinet.required' => 'L\'email du cabinet est obligatoire.',
            'email_cabinet.email' => 'L\'email du cabinet doit être une adresse email valide.',
            'email_cabinet.max' => 'L\'email du cabinet ne peut dépasser 255 caractères.',
            
            'telephone_cabinet.string' => 'Le téléphone du cabinet doit être du texte.',
            'telephone_cabinet.max' => 'Le téléphone du cabinet ne peut dépasser 20 caractères.',
            
            'adresse_cabinet.string' => 'L\'adresse du cabinet doit être du texte.',
            'adresse_cabinet.max' => 'L\'adresse du cabinet ne peut dépasser 500 caractères.',
            
            'recommandation_adaptation.required' => 'La recommandation d\'adaptation est obligatoire.',
            'recommandation_adaptation.string' => 'La recommandation d\'adaptation doit être du texte.',
            'recommandation_adaptation.min' => 'La recommandation d\'adaptation doit contenir au moins 100 caractères.',
            'recommandation_adaptation.max' => 'La recommandation d\'adaptation ne peut dépasser 5000 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'fichier_rapport' => 'fichier rapport',
            'nom_cabinet' => 'nom du cabinet',
            'contact_cabinet' => 'contact du cabinet',
            'email_cabinet' => 'email du cabinet',
            'telephone_cabinet' => 'téléphone du cabinet',
            'adresse_cabinet' => 'adresse du cabinet',
            'recommandation_adaptation' => 'recommandation d\'adaptation',
        ];
    }
}