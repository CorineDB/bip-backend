<?php

namespace App\Http\Requests\tdrs_prefaisabilite;

use App\Repositories\DocumentRepository;
use App\Repositories\TdrRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprecierTdrPrefaisabiliteRequest extends FormRequest
{
    protected $champs = [];

    protected $appreciations = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        /*return auth()->check()  && auth()->user()->type === 'dpaf' */;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'evaluer' => 'required|boolean:true',
            'evaluations_champs' => 'required|array|min:'. /* $this->input("evaluer") ? count($this->champs) :  */1,
            'evaluations_champs.*.champ_id' => ["required", "in:".implode(",", $this->champs), Rule::exists("champs", "id",)],
            'evaluations_champs.*.appreciation' => 'required|in:'.implode(",", $this->appreciations),
            'evaluations_champs.*.commentaire' => 'required|string|min:50'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'options_notation.required' => 'Les options de notation sont obligatoires.',
            'options_notation.array' => 'Les options de notation doivent être un tableau.',
            'options_notation.min' => 'Au moins 2 options de notation sont requises.',
            'options_notation.*.required' => 'Chaque option de notation doit avoir une valeur.',
            'options_notation.*.string' => 'Les options de notation doivent être du texte.',
            'options_notation.*.max' => 'Chaque option ne peut dépasser 255 caractères.',
            'criteres_evaluation.array' => 'Les critères d\'évaluation doivent être un tableau.',
            'criteres_evaluation.seuil_acceptation.numeric' => 'Le seuil d\'acceptation doit être un nombre.',
            'criteres_evaluation.seuil_acceptation.min' => 'Le seuil d\'acceptation ne peut être inférieur à 0.',
            'criteres_evaluation.seuil_acceptation.max' => 'Le seuil d\'acceptation ne peut dépasser 100.',
            'criteres_evaluation.commentaire_obligatoire.boolean' => 'Le champ commentaire obligatoire doit être vrai ou faux.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'options_notation' => 'options de notation',
            'criteres_evaluation.seuil_acceptation' => 'seuil d\'acceptation',
            'criteres_evaluation.commentaire_obligatoire' => 'commentaire obligatoire',
        ];
    }

    public function prepareForValidation(){
        $canevas = app()->make(DocumentRepository::class)->getModel()
                                            ->where('type', 'formulaire')
                                            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-tdr-prefaisabilite'))
                                            ->orderBy('created_at', 'desc')->first();

        $evaluationConfigs = $canevas->evaluation_configs;

        $this->appreciations = collect($evaluationConfigs['options_notation'] ?? [])->pluck('appreciation')->toArray();

        $noteConceptuelle = app()->make(TdrRepository::class)->getModel()
            ->findOrFail($this->route("noteId"));

        $this->champs = collect($noteConceptuelle->note_conceptuelle)->pluck("id")->toArray();
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {

        /*
        $validator->after(function ($validator) {
            // Vérifier que les clés des options sont valides
            $optionsNotation = $this->input('options_notation', []);
            $clesValides = ['passe', 'retour', 'non_accepte'];

            foreach (array_keys($optionsNotation) as $cle) {
                if (!in_array($cle, $clesValides)) {
                    $validator->errors()->add(
                        "options_notation.{$cle}",
                        "La clé '{$cle}' n'est pas une option de notation valide. Utilisez: " . implode(', ', $clesValides)
                    );
                }
            }

            // Vérifier qu'au moins les options essentielles sont présentes
            $optionsEssentielles = ['passe', 'retour', 'non_accepte'];
            foreach ($optionsEssentielles as $option) {
                if (!isset($optionsNotation[$option]) || empty($optionsNotation[$option])) {
                    $validator->errors()->add(
                        "options_notation.{$option}",
                        "L'option '{$option}' est obligatoire."
                    );
                }
            }
        });
        */
    }
}