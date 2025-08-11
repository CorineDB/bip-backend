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
    protected $ideeProjet;
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

        $this->ideeProjet = IdeeProjet::findOrFail($ideeProjetId);

        if ($this->ideeProjet->statut != StatutIdee::IDEE_DE_PROJET) {
            throw ValidationException::withMessages(["Vous le statut de l'idee de projet est a ". $this->ideeProjet->statut->value]);
        }

    }

    public function rules(): array
    {
        if($this->input("decision") == "valider" && $this->ideeProjet->score_climatique < 0.67){
            throw ValidationException::withMessages(["Score de l'impact climatique est insastifaisant"], 403);
        }

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
