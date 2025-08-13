<?php

namespace App\Http\Requests\evaluations;

use App\Enums\StatutIdee;
use App\Models\IdeeProjet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ValidationIdeeProjetAProjetRequest extends FormRequest
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

        /* if ($this->ideeProjet->statut != StatutIdee::VALIDATION) {
            throw ValidationException::withMessages([" Uniquement les idees etant a l'etape d'analyse pourront etre valider, cette idee de projet est a l'etape ". $this->ideeProjet->statut->value]);
        } */

    }

    public function rules(): array
    {
        if($this->input("decision") == "valider" && $this->ideeProjet->score_amc < 0.67){
            throw ValidationException::withMessages(["Score de l'analyse multi-critere est insastifaisant"], 403);
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
