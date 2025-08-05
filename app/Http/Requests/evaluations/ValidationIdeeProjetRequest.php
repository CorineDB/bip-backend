<?php

namespace App\Http\Requests\evaluations;

use App\Enums\StatutIdee;
use App\Models\CategorieCritere;
use App\Models\Evaluation;
use App\Models\EvaluationCritere;
use App\Models\IdeeProjet;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ValidationIdeeProjetRequest extends FormRequest
{
    protected $categorieCritere;
    protected $evaluation;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $ideeProjetId = $this->route('ideeProjetId');

        $ideeProjet = IdeeProjet::findOrFail($ideeProjetId);

        if ($ideeProjet->statut != StatutIdee::IDEE_DE_PROJET) {
            throw ValidationException::withMessages(["Vous le statut de l'idee de projet est a ". $ideeProjet->statut->value]);
        }
    }

    public function rules(): array
    {
        return [
            'decision' => ["required", "in:valider,rejeter"],
            'commentaire' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }
}
