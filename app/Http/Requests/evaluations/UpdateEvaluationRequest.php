<?php

namespace App\Http\Requests\evaluations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type_evaluation' => ["required", "string", "in:climatique,amc,note_conceptuelle"],
            'commentaire' => 'nullable|string|max:1000',

            // Assignation des évaluateurs aux critères
            'criteres' => 'required|array|min:1',
            'criteres.*.critere_id' => 'required|integer|exists:criteres,id',
            'criteres.*.evaluateur_id' => 'required|integer|exists:users,id',
            'criteres.*.categorie_critere_id' => ["required","integer", Rule::exists('categories_critere', 'id')->whereNull('deleted_at')],
        ];
    }
}
