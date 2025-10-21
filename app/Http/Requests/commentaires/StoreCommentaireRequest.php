<?php

namespace App\Http\Requests\commentaires;

use App\Models\Commentaire;
use App\Models\EvaluationChamp;
use App\Models\Projet;
use App\Rules\HashedExists;
use Exception;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {

        $type = $this->input('commentaireable_type');
        $map = Commentaire::getCommentaireableMap();

        $typeLower = strtolower($type);

        $modelClass = null;
        if (isset($map[$typeLower])) {
            $modelClass = $map[$typeLower];
        } else {
            $modelClass = Projet::class;
        }

        return [
            'commentaire' => ['required', 'string', 'min:1', 'max:5000'],
            'commentaire_id' => ['nullable', new HashedExists(Commentaire::class)],
            'commentaireable_type' => ['required', "string", "max:100"],
            'commentaireable_id' => ['required',  new HashedExists($modelClass)],
            // Règles pour les fichiers
            'fichiers' => ['nullable', 'array', 'max:5'],
            'fichiers.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt'],
        ];
    }

    public function messages(): array
    {
        return [
            // Commentaire
            'commentaire.required' => 'Le contenu du commentaire est obligatoire.',
            'commentaire.string' => 'Le contenu du commentaire doit être une chaîne de caractères.',
            'commentaire.min' => 'Le commentaire doit contenir au moins 10 caractères.',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 5000 caractères.',

            // Sous-commentaire (parent)
            'commentaire_id.integer' => 'L’identifiant du commentaire parent doit être un entier.',
            'commentaire_id.exists' => 'Le commentaire parent spécifié n’existe pas ou a été supprimé.',

            // Polymorphique
            'commentaireable_type.required' => 'Le type de la ressource commentée est obligatoire.',
            'commentaireable_type.string' => 'Le type de ressource commentée doit être une chaîne de caractères.',
            'commentaireable_type.max' => 'Le type de ressource commentée ne doit pas dépasser 255 caractères.',
            'commentaireable_type.in' => 'Le type de ressource commentée n’est pas valide.',

            'commentaireable_id.required' => "L'identifiant de la ressource commentée est obligatoire.",
            'commentaireable_id.integer' => "L'identifiant de la ressource commentée doit être un entier.",
            'commentaireable_id.min' => "L'identifiant de la ressource commentée doit être supérieur à 0.",
            'commentaireable_id.exists' => "La ressource commentée spécifiée n'existe pas.",

            // Fichiers
            'fichiers.array' => 'Les fichiers doivent être fournis sous forme de tableau.',
            'fichiers.max' => 'Vous ne pouvez pas joindre plus de 5 fichiers.',
            'fichiers.*.file' => 'Chaque élément doit être un fichier valide.',
            'fichiers.*.max' => 'Chaque fichier ne doit pas dépasser 10 Mo.',
            'fichiers.*.mimes' => 'Les fichiers doivent être de type: pdf, jpg, jpeg, png, doc, docx, xls, xlsx, txt.'
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->commentaireable_type) {
            $type = strtolower($this->commentaireable_type);

            $articles = Commentaire::$resourceArticles;

            $article = $articles[$type] ?? 'la'; // par défaut "la"

            // transformer le type en format lisible (majuscule première lettre, underscore → espace)
            $nom = ucfirst(str_replace('_', ' ', $type));

            $this->merge([
                'commentaireable_name' => "$article $nom",
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            /* $type = $this->input('commentaireable_type');
            $map = Commentaire::getCommentaireableMap();

            $typeLower = strtolower($type);

            if (isset($map[$typeLower])) {
                $modelClass = $map[$typeLower];

                $idValidator = new HashedExists($modelClass);

                if ($idValidator && !$idValidator->passes("commentaireable_id", $this->input('commentaireable_id'))) {
                    $validator->errors()->add("commentaireable_id", $idValidator->message());
                }
                throw new Exception(json_encode(request()->all()), 403);
            } else {
                $rules['commentaireable_type'] = ['required', 'string', 'max:255'];
                $rules['commentaireable_id'] = ['required', 'min:1'];
            } */

            if ($this->has('commentaireable_name')) {
                $validator->setCustomMessages([
                    'commentaireable_id.exists' => $this->commentaireable_name . " spécifié(e) n'existe pas ou a été supprimé(e).",
                ]);
            }

            $this->merge(["commentaireable_id" => request()->get("commentaireable_id")]);

            // Récupérer les valeurs déhashées et les merger
            $unhashedValues = $this->attributes->get('_unhashed_values', []);
            if (!empty($unhashedValues)) {
                $this->merge($unhashedValues);
            }
        });
    }
}
