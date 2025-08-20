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
        return true; //auth()->check() && in_array(auth()->user()->type, ['dpaf', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rapport' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480', // Max 20MB
            "cabinet_etude" => ["array", "min:4"],
            'cabinet_etude.nom_cabinet' => 'required|string|max:255',
            'cabinet_etude.contact_cabinet' => 'required|string|max:255',
            'cabinet_etude.email_cabinet' => 'required|email|max:255',
            'cabinet_etude.adresse_cabinet' => 'required|string|max:500',
            'recommandation' => 'required|string|max:500'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'rapport.required' => 'Le fichier rapport est obligatoire.',
            'rapport.file' => 'Le rapport doit être un fichier.',
            'rapport.mimes' => 'Le rapport doit être un fichier PDF, DOC ou DOCX.',
            'rapport.max' => 'Le fichier rapport ne peut dépasser 20 MB.',

            'cabinet_etude.nom_cabinet.required' => 'Le nom du cabinet est obligatoire.',
            'cabinet_etude.nom_cabinet.string' => 'Le nom du cabinet doit être du texte.',
            'cabinet_etude.nom_cabinet.max' => 'Le nom du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.contact_cabinet.required' => 'Le contact du cabinet est obligatoire.',
            'cabinet_etude.contact_cabinet.string' => 'Le contact du cabinet doit être du texte.',
            'cabinet_etude.contact_cabinet.max' => 'Le contact du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.email_cabinet.required' => 'L\'email du cabinet est obligatoire.',
            'cabinet_etude.email_cabinet.email' => 'L\'email du cabinet doit être une adresse email valide.',
            'cabinet_etude.email_cabinet.max' => 'L\'email du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.telephone_cabinet.string' => 'Le téléphone du cabinet doit être du texte.',
            'cabinet_etude.telephone_cabinet.max' => 'Le téléphone du cabinet ne peut dépasser 20 caractères.',

            'cabinet_etude.adresse_cabinet.string' => 'L\'adresse du cabinet doit être du texte.',
            'cabinet_etude.adresse_cabinet.max' => 'L\'adresse du cabinet ne peut dépasser 500 caractères.'
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