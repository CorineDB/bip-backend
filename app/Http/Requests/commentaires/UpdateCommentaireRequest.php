<?php

namespace App\Http\Requests\commentaires;

use App\Models\Commentaire;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommentaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Optionnel : vérifier que l'utilisateur est le commentateur
        return auth()->check() /*&& $this->commentaire->commentateurId === auth()->id()*/;
    }

    public function rules(): array
    {
        $rules = [
            'commentaire' => ['nullable', 'string', 'min:10', 'max:5000'],

            // Règles pour les fichiers
            'fichiers' => ['nullable', 'array', 'max:5'],
            'fichiers.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt'],

            // IDs des fichiers à supprimer
            'fichiers_a_supprimer' => ['nullable', 'array'],
            'fichiers_a_supprimer.*' => [Rule::exists('fichiers', 'id')->whereNull('deleted_at')],
        ];

        // Si on autorise le déplacement du commentaire vers une autre ressource
        $type = $this->input('commentaireable_type');

        if ($type) {
            $map = array_map(fn($class) => (new $class)->getTable(), Commentaire::getCommentaireableMap());
            $typeLower = strtolower($type);

            if (isset($map[$typeLower])) {
                $rules['commentaireable_type'] = ['required', 'string'];
                $rules['commentaireable_id'] = [
                    'required',
                    Rule::exists($map[$typeLower], 'id')
                ];
            } else {
                $rules['commentaireable_type'] = ['required', 'string', 'max:255'];
                $rules['commentaireable_id'] = ['required', 'min:1'];
            }
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if ($this->commentaireable_type) {
            $type = strtolower($this->commentaireable_type);

            $articles = Commentaire::$resourceArticles;
            $article = $articles[$type] ?? 'la';
            $nom = ucfirst(str_replace('_', ' ', $type));

            $this->merge([
                'commentaireable_name' => "$article $nom",
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('commentaireable_name')) {
                $validator->setCustomMessages([
                    'commentaireable_id.exists' => $this->commentaireable_name . ' spécifié(e) n’existe pas ou a été supprimé(e).',
                ]);
            }
        });
    }

    public function messages(): array
    {
        return [
            'commentaire.required' => 'Le contenu du commentaire est obligatoire.',
            'commentaire.string' => 'Le contenu du commentaire doit être du texte.',
            'commentaire.min' => 'Le commentaire doit contenir au moins 10 caractères.',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 5000 caractères.',
            'commentaireable_type.required' => 'Le type de la ressource commentée est obligatoire.',
            'commentaireable_type.string' => 'Le type de la ressource commentée doit être du texte.',
            'commentaireable_type.max' => 'Le type de la ressource commentée ne doit pas dépasser 255 caractères.',
            'commentaireable_id.required' => 'L'identifiant de la ressource commentée est obligatoire.',
            'commentaireable_id.integer' => 'L'identifiant de la ressource commentée doit être un nombre entier.',
            'commentaireable_id.min' => 'L'identifiant de la ressource commentée doit être supérieur à 0.',

            // Fichiers
            'fichiers.array' => 'Les fichiers doivent être fournis sous forme de tableau.',
            'fichiers.max' => 'Vous ne pouvez pas joindre plus de 5 fichiers.',
            'fichiers.*.file' => 'Chaque élément doit être un fichier valide.',
            'fichiers.*.max' => 'Chaque fichier ne doit pas dépasser 10 Mo.',
            'fichiers.*.mimes' => 'Les fichiers doivent être de type: pdf, jpg, jpeg, png, doc, docx, xls, xlsx, txt.',

            // Fichiers à supprimer
            'fichiers_a_supprimer.array' => 'Les IDs des fichiers à supprimer doivent être fournis sous forme de tableau.',
            'fichiers_a_supprimer.*.integer' => 'Chaque ID de fichier doit être un nombre entier.',
            'fichiers_a_supprimer.*.exists' => 'Le fichier spécifié n\'existe pas ou a déjà été supprimé.',
        ];
    }
}
