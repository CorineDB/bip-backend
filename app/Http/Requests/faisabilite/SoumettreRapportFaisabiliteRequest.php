<?php

namespace App\Http\Requests\faisabilite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Projet;
use App\Models\Notation;
use App\Models\CategorieCritere;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class SoumettreRapportFaisabiliteRequest extends FormRequest
{
    protected $champsAssuranceQualite = [];
    protected $champsTechnique = [];
    protected $champsEconomique = [];
    protected $champsMarche = [];
    protected $champsOrganisationnelleJuridique = [];
    protected $champsAnalyseFinanciere = [];
    protected $champsImpactEnvironnemental = [];
    protected $projet = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //auth()->check() && in_array(auth()->user()->type, ['responsable_projet', 'dpaf', 'admin']);
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        // Récupérer l'ID du projet depuis la route
        $projetId = $this->route('projetId');
        if (!$projetId) {
            return;
        }

        // Récupérer le projet avec ses relations
        $this->projet = Projet::with('secteur.parent')->findOrFail($projetId);

        // Préparer tous les checklists de faisabilité
        $this->prepareAllChecklists();
    }

    /**
     * Préparer tous les checklists de faisabilité
     */
    private function prepareAllChecklists(): void
    {
        // 1. Assurance qualité
        $canevasAssurance = $this->getChecklistBySlug('canevas-check-liste-suivi-assurance-qualite-rapport-etude-faisabilite');
        if (!empty($canevasAssurance)) {
            $champsValides = $this->extractAllFields($canevasAssurance);
            $this->champsAssuranceQualite = collect($champsValides)->pluck('id')->filter()->toArray();
        }

        // 2. Technique
        $canevasTechnique = $this->getChecklistBySlug('canevas-check-liste-etude-faisabilite-technique');
        if (!empty($canevasTechnique)) {
            $champsValides = $this->extractAllFields($canevasTechnique);
            $this->champsTechnique = collect($champsValides)->pluck('id')->filter()->toArray();
        }

        // 3. Économique
        $canevasEconomique = $this->getChecklistBySlug('canevas-check-liste-etude-faisabilite-economique');
        if (!empty($canevasEconomique)) {
            $champsValides = $this->extractAllFields($canevasEconomique);
            $this->champsEconomique = collect($champsValides)->pluck('id')->filter()->toArray();
        }

        // 4. Marché
        $canevasMarche = $this->getChecklistBySlug('canevas-check-liste-etude-faisabilite-marche');
        if (!empty($canevasMarche)) {
            $champsValides = $this->extractAllFields($canevasMarche);
            $this->champsMarche = collect($champsValides)->pluck('id')->filter()->toArray();
        }

        // 5. Organisationnelle et juridique
        $canevasOrgJur = $this->getChecklistBySlug('canevas-check-liste-de-suivi-etude-de-faisabilite-organisationnelle-juridique');
        if (!empty($canevasOrgJur)) {
            $champsValides = $this->extractAllFields($canevasOrgJur);
            $this->champsOrganisationnelleJuridique = collect($champsValides)->pluck('id')->filter()->toArray();
        }

        // 6. Analyse financière
        $canevasFinanciere = $this->getChecklistBySlug('canevas-check-liste-de-suivi-analyse-de-faisabilite-financiere');
        if (!empty($canevasFinanciere)) {
            $champsValides = $this->extractAllFields($canevasFinanciere);
            $this->champsAnalyseFinanciere = collect($champsValides)->pluck('id')->filter()->toArray();
        }

        // 7. Impact environnemental et social
        $canevasImpact = $this->getChecklistBySlug('canevas-check-liste-de-suivi-etude-analyse-impact-environnementale-sociale');
        if (!empty($canevasImpact)) {
            $champsValides = $this->extractAllFields($canevasImpact);
            $this->champsImpactEnvironnemental = collect($champsValides)->pluck('id')->filter()->toArray();
        }

        // Conserver la variable $this->champs pour compatibilité avec le code existant (assurance qualité)
        //$this->champs = $this->champsAssuranceQualite;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Action: submit (soumettre) ou draft (brouillon)
            'action' => 'required|string|in:submit,draft',

            // Fichiers requis uniquement pour la soumission finale
            'rapport' => 'required_unless:action,draft|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'proces_verbal' => 'required_unless:action,draft|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'liste_presence' => 'required_unless:action,draft|file|mimes:pdf,doc,docx,xls,xlsx,png,jpeg,jpg|max:20480',

            // Informations cabinet requises uniquement pour la soumission finale
            "cabinet_etude" => "required_unless:action,draft|array|min:4",
            'cabinet_etude.nom_cabinet' => 'required_unless:action,draft|string|max:255',
            'cabinet_etude.contact_cabinet' => 'required_unless:action,draft|string|max:255',
            'cabinet_etude.email_cabinet' => 'required_unless:action,draft|email|max:255',
            'cabinet_etude.adresse_cabinet' => 'required_unless:action,draft|string|max:500',

            // Recommandation requise uniquement pour la soumission finale
            'recommandation' => 'required_unless:action,draft|string|max:500',

            // 1. Checklist de suivi assurance qualité rapport étude faisabilité
            'checklist_suivi_assurance_qualite' => [
                'required_unless:action,draft',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : 'min:' . count($this->champsAssuranceQualite)
            ],
            'checklist_suivi_assurance_qualite.*.checkpoint_id' => ['required', "in:" . implode(",", $this->champsAssuranceQualite)],

            // 2. Checklist étude faisabilité technique
            'checklist_etude_faisabilite_technique' => [
                'required_unless:action,draft',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : 'min:' . count($this->champsTechnique)
            ],
            'checklist_etude_faisabilite_technique.*.checkpoint_id' => ['required', "in:" . implode(",", $this->champsTechnique)],

            // 3. Checklist étude faisabilité économique
            'checklist_etude_faisabilite_economique' => [
                'required_unless:action,draft',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : 'min:' . count($this->champsEconomique)
            ],
            'checklist_etude_faisabilite_economique.*.checkpoint_id' => ['required', "in:" . implode(",", $this->champsEconomique)],

            // 4. Checklist étude faisabilité marché
            'checklist_etude_faisabilite_marche' => [
                'required_unless:action,draft',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : 'min:' . count($this->champsMarche)
            ],
            'checklist_etude_faisabilite_marche.*.checkpoint_id' => ['required', "in:" . implode(",", $this->champsMarche)],

            // 5. Checklist étude faisabilité organisationnelle et juridique
            'checklist_etude_faisabilite_organisationnelle_juridique' => [
                'required_unless:action,draft',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : 'min:' . count($this->champsOrganisationnelleJuridique)
            ],
            'checklist_etude_faisabilite_organisationnelle_juridique.*.checkpoint_id' => ['required', "in:" . implode(",", $this->champsOrganisationnelleJuridique)],

            // 6. Checklist suivi analyse de faisabilité financière
            'checklist_suivi_analyse_faisabilite_financiere' => [
                'required_unless:action,draft',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : 'min:' . count($this->champsAnalyseFinanciere)
            ],
            'checklist_suivi_analyse_faisabilite_financiere.*.checkpoint_id' => ['required', "in:" . implode(",", $this->champsAnalyseFinanciere)],

            // 7. Checklist suivi étude analyse impact environnementale et sociale
            'checklist_suivi_etude_analyse_impact_environnemental_social' => [
                'required_unless:action,draft',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : 'min:' . count($this->champsImpactEnvironnemental)
            ],
            'checklist_suivi_etude_analyse_impact_environnemental_social.*.checkpoint_id' => ['required', "in:" . implode(",", $this->champsImpactEnvironnemental)],



            "analyse_financiere"                            => "sometimes|required_unless:action,draft|array|min:3",
            'analyse_financiere.duree_vie'                  => 'sometimes|required_unless:action,draft|numeric',
            'analyse_financiere.taux_actualisation'         => 'sometimes|required_unless:action,draft|numeric',
            'analyse_financiere.investissement_initial'     => 'sometimes|required_unless:action,draft|numeric',
            'analyse_financiere.flux_tresorerie'            => 'sometimes|required_unless:action,draft|array|min:' . $this->input("analyse_financiere.duree_vie") ?? 1,
            'analyse_financiere.flux_tresorerie.*.t'        => 'sometimes|required_unless:action,draft|numeric|min:' . 1 . '|max:' . $this->input("analyse_financiere.duree_vie") ?? 1,
            'analyse_financiere.flux_tresorerie.*.CFt'      => 'sometimes|required_unless:action,draft|numeric|min:0',

            "etude_faisabilite" => "required_unless:action,draft|array|min:1",
            'etude_faisabilite.est_finance' => 'required_with:etude_faisabilite|boolean',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Valider toutes les checklists d'études de faisabilité
            $this->validateChecklistSuiviAssuranceQualite($validator);
            $this->validateChecklistEtudeFaisabiliteTechnique($validator);
            $this->validateChecklistEtudeFaisabiliteEconomique($validator);
            $this->validateChecklistEtudeFaisabiliteMarche($validator);
            $this->validateChecklistEtudeFaisabiliteOrganisationnelleJuridique($validator);
            $this->validateChecklistSuiviAnalyseFaisabiliteFinanciere($validator);
            $this->validateChecklistSuiviEtudeAnalyseImpactEnvironnementalSocial($validator);
        });
    }

    /**
     * Valider une checklist de façon générique
     */
    private function validateChecklist(Validator $validator, string $checklistName, array $champsValides, string $slug): void
    {
        $checklistData = $this->input($checklistName);
        if (!$checklistData || !is_array($checklistData)) {
            return;
        }

        $estSoumise = $this->input('action', 'submit') === 'submit';
        $canevasFields = $this->getCanevasFieldsWithConfigsBySlug($slug);

        foreach ($checklistData as $index => $evaluation) {
            $checkpointId = $evaluation['checkpoint_id'] ?? null;
            $remarque = $evaluation['remarque'] ?? null;
            $explication = $evaluation['explication'] ?? null;

            // Vérifier que le checkpoint_id existe dans le canevas
            if ($checkpointId && !in_array($checkpointId, $champsValides)) {
                $validator->errors()->add(
                    "{$checklistName}.{$index}.checkpoint_id",
                    "Le champ sélectionné n'appartient pas à la checklist {$checklistName}."
                );
                continue;
            }

            // Récupérer la configuration du champ
            $fieldConfig = $canevasFields[$checkpointId] ?? null;

            // Validation de la remarque
            if ($estSoumise) {
                if (empty($remarque)) {
                    $validator->errors()->add(
                        "{$checklistName}.{$index}.remarque",
                        "La remarque est obligatoire pour la soumission finale."
                    );
                } else {
                    $this->validateRemarqueValueGeneric($validator, $checklistName, $index, $remarque, $fieldConfig);
                }
            } else {
                if ($remarque !== null) {
                    $this->validateRemarqueValueGeneric($validator, $checklistName, $index, $remarque, $fieldConfig);
                }
            }

            // Validation de l'explication selon show_explanation
            $this->validateExplicationGeneric($validator, $checklistName, $index, $explication, $fieldConfig, $estSoumise);
        }

        // Vérifier que tous les champs obligatoires sont présents pour la soumission finale
        if ($estSoumise && !empty($champsValides)) {
            $champsEvalues = collect($checklistData)->pluck('checkpoint_id')->toArray();
            $champsManquants = array_diff($champsValides, $champsEvalues);

            if (!empty($champsManquants)) {
                $validator->errors()->add(
                    $checklistName,
                    "Tous les champs de la checklist {$checklistName} doivent être évalués pour la soumission finale. Champs manquants: " . implode(', ', $champsManquants)
                );
            }
        }
    }

    // Méthodes de validation spécifiques pour chaque checklist
    private function validateChecklistSuiviAssuranceQualite(Validator $validator): void
    {
        $this->validateChecklist($validator, 'checklist_suivi_assurance_qualite', $this->champsAssuranceQualite, 'canevas-check-liste-suivi-assurance-qualite-rapport-etude-faisabilite');
    }

    private function validateChecklistEtudeFaisabiliteTechnique(Validator $validator): void
    {
        $this->validateChecklist($validator, 'checklist_etude_faisabilite_technique', $this->champsTechnique, 'canevas-check-liste-etude-faisabilite-technique');
    }

    private function validateChecklistEtudeFaisabiliteEconomique(Validator $validator): void
    {
        $this->validateChecklist($validator, 'checklist_etude_faisabilite_economique', $this->champsEconomique, 'canevas-check-liste-etude-faisabilite-economique');
    }

    private function validateChecklistEtudeFaisabiliteMarche(Validator $validator): void
    {
        $this->validateChecklist($validator, 'checklist_etude_faisabilite_marche', $this->champsMarche, 'canevas-check-liste-etude-faisabilite-marche');
    }

    private function validateChecklistEtudeFaisabiliteOrganisationnelleJuridique(Validator $validator): void
    {
        $this->validateChecklist($validator, 'checklist_etude_faisabilite_organisationnelle_juridique', $this->champsOrganisationnelleJuridique, 'canevas-check-liste-de-suivi-etude-de-faisabilite-organisationnelle-juridique');
    }

    private function validateChecklistSuiviAnalyseFaisabiliteFinanciere(Validator $validator): void
    {
        $this->validateChecklist($validator, 'checklist_suivi_analyse_faisabilite_financiere', $this->champsAnalyseFinanciere, 'canevas-check-liste-de-suivi-analyse-de-faisabilite-financiere');
    }

    private function validateChecklistSuiviEtudeAnalyseImpactEnvironnementalSocial(Validator $validator): void
    {
        $this->validateChecklist($validator, 'checklist_suivi_etude_analyse_impact_environnemental_social', $this->champsImpactEnvironnemental, 'canevas-check-liste-de-suivi-etude-analyse-impact-environnementale-sociale');
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'rapport_faisabilite.required_unless' => 'Le rapport de faisabilité est obligatoire pour la soumission finale.',
            'rapport_faisabilite.file' => 'Le rapport de faisabilité doit être un fichier.',
            'rapport_faisabilite.mimes' => 'Le rapport de faisabilité doit être un fichier PDF, DOC, DOCX, XLS ou XLSX.',
            'rapport_faisabilite.max' => 'Le rapport de faisabilité ne peut dépasser 20 MB.',

            'rapport_couts_avantages.required_unless' => 'Le rapport des coûts et avantages sociaux est obligatoire pour la soumission finale.',
            'rapport_couts_avantages.file' => 'Le rapport des coûts et avantages doit être un fichier.',
            'rapport_couts_avantages.mimes' => 'Le rapport des coûts et avantages doit être un fichier PDF, DOC, DOCX, XLS ou XLSX.',
            'rapport_couts_avantages.max' => 'Le rapport des coûts et avantages ne peut dépasser 20 MB.',

            'liste_presence.file' => 'La liste de présence doit être un fichier.',
            'liste_presence.mimes' => 'La liste de présence doit être un fichier PDF, DOC, DOCX, XLS ou XLSX.',
            'liste_presence.max' => 'La liste de présence ne peut dépasser 20 MB.',

            'cabinet_etude.required_unless' => 'Les informations du cabinet sont obligatoires pour la soumission finale.',
            'cabinet_etude.nom_cabinet.required_with' => 'Le nom du cabinet est obligatoire.',
            'cabinet_etude.nom_cabinet.string' => 'Le nom du cabinet doit être du texte.',
            'cabinet_etude.nom_cabinet.max' => 'Le nom du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.contact_cabinet.required_with' => 'Le contact du cabinet est obligatoire.',
            'cabinet_etude.contact_cabinet.string' => 'Le contact du cabinet doit être du texte.',
            'cabinet_etude.contact_cabinet.max' => 'Le contact du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.email_cabinet.required_with' => 'L\'email du cabinet est obligatoire.',
            'cabinet_etude.email_cabinet.email' => 'L\'email du cabinet doit être une adresse email valide.',
            'cabinet_etude.email_cabinet.max' => 'L\'email du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.adresse_cabinet.required_with' => 'L\'adresse du cabinet est obligatoire.',
            'cabinet_etude.adresse_cabinet.string' => 'L\'adresse du cabinet doit être du texte.',
            'cabinet_etude.adresse_cabinet.max' => 'L\'adresse du cabinet ne peut dépasser 500 caractères.',

            'recommandation.required_unless' => 'La recommandation est obligatoire pour la soumission finale.',

            // Messages pour l'action
            'action.in' => 'L\'action doit être "submit" ou "draft".',

            // Messages pour les checklists d'études de faisabilité
            'checklist_suivi_assurance_qualite.required_unless' => 'La checklist de suivi assurance qualité est obligatoire pour la soumission finale.',
            'checklist_suivi_assurance_qualite.array' => 'La checklist de suivi assurance qualité doit être un tableau.',

            'checklist_etude_faisabilite_technique.required_unless' => 'La checklist d\'étude de faisabilité technique est obligatoire pour la soumission finale.',
            'checklist_etude_faisabilite_technique.array' => 'La checklist d\'étude de faisabilité technique doit être un tableau.',

            'checklist_etude_faisabilite_economique.required_unless' => 'La checklist d\'étude de faisabilité économique est obligatoire pour la soumission finale.',
            'checklist_etude_faisabilite_economique.array' => 'La checklist d\'étude de faisabilité économique doit être un tableau.',

            'checklist_etude_faisabilite_marche.required_unless' => 'La checklist d\'étude de faisabilité marché est obligatoire pour la soumission finale.',
            'checklist_etude_faisabilite_marche.array' => 'La checklist d\'étude de faisabilité marché doit être un tableau.',

            'checklist_etude_faisabilite_organisationnelle_juridique.required_unless' => 'La checklist d\'étude de faisabilité organisationnelle et juridique est obligatoire pour la soumission finale.',
            'checklist_etude_faisabilite_organisationnelle_juridique.array' => 'La checklist d\'étude de faisabilité organisationnelle et juridique doit être un tableau.',

            'checklist_suivi_analyse_faisabilite_financiere.required_unless' => 'La checklist de suivi analyse de faisabilité financière est obligatoire pour la soumission finale.',
            'checklist_suivi_analyse_faisabilite_financiere.array' => 'La checklist de suivi analyse de faisabilité financière doit être un tableau.',

            'checklist_suivi_etude_analyse_impact_environnemental_social.required_unless' => 'La checklist de suivi étude analyse d\'impact environnemental et social est obligatoire pour la soumission finale.',
            'checklist_suivi_etude_analyse_impact_environnemental_social.array' => 'La checklist de suivi étude analyse d\'impact environnemental et social doit être un tableau.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'rapport_faisabilite' => 'rapport de faisabilité',
            'rapport_couts_avantages' => 'rapport des coûts et avantages sociaux',
            'liste_presence' => 'liste de présence',
            'cabinet_etude.nom_cabinet' => 'nom du cabinet',
            'cabinet_etude.contact_cabinet' => 'contact du cabinet',
            'cabinet_etude.email_cabinet' => 'email du cabinet',
            'cabinet_etude.adresse_cabinet' => 'adresse du cabinet',
            'recommandation' => 'recommandation',
        ];
    }

    /**
     * Aplati toutes les sections pour ne garder que les champs.
     */
    private function extractAllFields(array $elements): array
    {
        $fields = [];
        foreach ($elements as $el) {
            // Si l'élément a un ID et des meta_options, c'est probablement un champ
            if (!empty($el['id']) && !empty($el['meta_options'])) {
                $fields[] = $el;
            }
            // Si c'est marqué explicitement comme un champ
            elseif (($el['element_type'] ?? null) === 'field') {
                $fields[] = $el;
            }
            // Récursion pour les éléments enfants (sections)
            if (!empty($el['elements']) && is_array($el['elements'])) {
                $fields = array_merge($fields, $this->extractAllFields($el['elements']));
            }
        }
        return $fields;
    }

    /**
     * Récupérer un checklist par son slug
     */
    protected function getChecklistBySlug(string $slug): array
    {
        $documentRepository = app(DocumentRepositoryInterface::class);
        $canevas = $documentRepository->getModel()
            ->where('type', 'checklist')
            ->whereHas('categorie', fn($q) => $q->where('slug', $slug))
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$canevas || !$canevas->all_champs) {
            return [];
        }

        // Convertir la Collection en array
        return $canevas->all_champs->toArray();
    }

    /**
     * Valider la valeur de la remarque selon les options du champ (version générique)
     */
    private function validateRemarqueValueGeneric(Validator $validator, string $checklistName, int $index, $remarque, ?array $fieldConfig): void
    {
        if (!$fieldConfig) return;

        $validationsRules = $fieldConfig['meta_options']['validations_rules'] ?? [];
        $validValues = $validationsRules['in'] ?? ['disponible', 'pas-encore-disponibles'];

        if (!in_array($remarque, $validValues)) {
            $validator->errors()->add(
                "{$checklistName}.{$index}.remarque",
                "La remarque doit être une des valeurs autorisées: " . implode(', ', $validValues)
            );
        }
    }

    /**
     * Valider l'explication selon la configuration show_explanation (version générique)
     */
    private function validateExplicationGeneric(Validator $validator, string $checklistName, int $index, $explication, ?array $fieldConfig, bool $estSoumise): void
    {
        if (!$fieldConfig) return;

        $showExplanation = $fieldConfig['meta_options']['configs']['show_explanation'] ?? false;
        $maxLength = $fieldConfig['meta_options']['configs']['explanation_max_length'] ?? 1000;

        // Si show_explanation est false, l'explication ne doit pas être requis même en submit
        if (!$showExplanation) {
            // L'explication est optionnel
            if ($explication !== null && strlen($explication) > $maxLength) {
                $validator->errors()->add(
                    "{$checklistName}.{$index}.explication",
                    "L'explication ne peut pas dépasser {$maxLength} caractères."
                );
            }
            return;
        }

        // Récupérer la remarque pour ce champ
        $remarque = $this->input("{$checklistName}.{$index}.remarque");
        $remarqueEstPasEncoreDisponible = $remarque === 'pas-encore-disponibles';

        // Si show_explanation est true, valider selon les règles
        if ($estSoumise) {
            // Pour submit, l'explication est obligatoire si show_explanation = true et remarque = "pas-encore-disponibles"
            if ($showExplanation && $remarqueEstPasEncoreDisponible) {
                if (empty($explication)) {
                    $validator->errors()->add(
                        "{$checklistName}.{$index}.explication",
                        "L'explication est obligatoire lorsque la remarque est 'pas-encore-disponibles' et que le champ d'explication est activé."
                    );
                } elseif (strlen($explication) < 10) {
                    $validator->errors()->add(
                        "{$checklistName}.{$index}.explication",
                        "L'explication doit contenir au moins 10 caractères."
                    );
                } elseif (strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "{$checklistName}.{$index}.explication",
                        "L'explication ne peut pas dépasser {$maxLength} caractères."
                    );
                }
            }
        } else {
            // Pour draft, l'explication devient obligatoire SEULEMENT si show_explanation = true ET remarque = "pas-encore-disponibles"
            if ($showExplanation && $remarqueEstPasEncoreDisponible) {
                /*if (empty($explication)) {
                    $validator->errors()->add(
                        "{$checklistName}.{$index}.explication",
                        "L'explication est obligatoire lorsque la remarque est 'pas-encore-disponibles' et que le champ d'explication est activé."
                    );
                } elseif (strlen($explication) < 10) {
                    $validator->errors()->add(
                        "{$checklistName}.{$index}.explication",
                        "L'explication doit contenir au moins 10 caractères."
                    );
                } elseif (strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "{$checklistName}.{$index}.explication",
                        "L'explication ne peut pas dépasser {$maxLength} caractères."
                    );
                }*/
            } else {
                // Pour draft dans tous les autres cas, l'explication est optionnel mais doit respecter les limites si présent
                /* if ($explication !== null && strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "{$checklistName}.{$index}.explication",
                        "L'explication ne peut pas dépasser {$maxLength} caractères."
                    );
                } */
            }
        }
    }

    /**
     * Récupérer les champs du canevas avec leurs configurations par slug
     */
    private function getCanevasFieldsWithConfigsBySlug(string $slug): array
    {
        $canevas = $this->getChecklistBySlug($slug);

        $fieldsWithConfigs = [];
        foreach ($canevas as $field) {
            if (!empty($field['id'])) {
                $fieldsWithConfigs[$field['id']] = $field;
            }
        }

        return $fieldsWithConfigs;
    }
}
