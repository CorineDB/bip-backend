<?php

namespace App\Models;

use App\Enums\PhasesIdee;
use App\Enums\SousPhaseIdee;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class IdeeProjet extends Model
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'idees_projet';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // Exemple : 'nom', 'programmeId'
        "est_soumise",
        "identifiant_bip",
        "identifiant_sigfp",
        "est_coherent",
        "ficheIdee",
        "statut",
        "phase",
        "sous_phase",
        "decision",
        "sigle",
        "type_projet",
        "parties_prenantes",
        "objectifs_specifiques",
        "resultats_attendus",
        "isdeleted",
        "body_projet",
        "cout_dollar_americain",
        "cout_euro",
        "date_debut_etude",
        "date_fin_etude",
        "date_prevue_demarrage",
        "date_effective_demarrage",
        "cout_dollar_canadien",
        "risques_immediats",
        "sommaire",
        "objectif_general",
        "conclusions",
        "description",
        "constats_majeurs",
        "public_cible",
        "estimation_couts",
        "description_decision",
        "impact_environnement",
        "aspect_organisationnel",
        "description_extrants",
        "caracteristiques",
        "score_climatique",
        "score_amc",
        'score_pertinence',
        "duree",
        "description_projet",
        "origine",
        "situation_desiree",
        "situation_actuelle",
        "contraintes",
        "echeancier",
        "fondement",
        "secteurId",
        "ministereId",
        "categorieId",
        "responsableId",
        "demandeurId",
        "demandeur_type",
        'porteur_projet',
        "titre_projet",
        'canevas_appreciation_pertinence',
        'canevas_climatique',
        'canevas_amc',
        'est_ancien'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'statut'     => StatutIdee::class,
        'phase'     => PhasesIdee::class,
        'sous_phase'     => SousPhaseIdee::class,
        'type_projet'     => TypesProjet::class,
        'est_ancien'      => 'boolean',
        // Seules les vraies colonnes JSON selon la migration
        'decision' => 'array',
        'cout_estimatif_projet' => 'array',
        'ficheIdee' => 'array',
        'parties_prenantes' => 'array',
        'objectifs_specifiques' => 'array',
        'resultats_attendus' => 'array',
        'body_projet' => 'array',
        'canevas_appreciation_pertinence' => 'array',
        'canevas_climatique' => 'array',
        'canevas_amc' => 'array',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        // Exemple : 'programmeId', 'updated_at', 'deleted_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $default = [
        "type_projet"   => TypesProjet::simple,
        "phase"         => PhasesIdee::identification,
        "sous_phase"    => SousPhaseIdee::redaction,
        "statut"        => StatutIdee::BROUILLON,
        // Exemple : 'programmeId', 'updated_at', 'deleted_at'
    ];

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {});

        static::saving(function ($model) {
            // Nettoyer les valeurs JSON vides avant la sauvegarde
            $model->cleanJsonAttributes();
        });
    }

    /**
     * Nettoyer les attributs JSON pour éviter les erreurs PostgreSQL
     */
    protected function cleanJsonAttributes(): void
    {
        $jsonColumns = [
            'decision',
            'cout_estimatif_projet',
            'ficheIdee',
            'parties_prenantes',
            'objectifs_specifiques',
            'resultats_attendus',
            'body_projet',
            'canevas_appreciation_pertinence',
            'canevas_climatique',
            'canevas_amc'
        ];

        foreach ($jsonColumns as $column) {
            if (!isset($this->attributes[$column])) {
                continue;
            }

            $value = $this->attributes[$column];

            // Si la valeur est une chaîne vide, la convertir en null ou array vide
            if ($value === '' || $value === null) {
                // Pour les colonnes obligatoires, utiliser un array vide
                if (in_array($column, ['ficheIdee', 'body_projet'])) {
                    $this->attributes[$column] = '[]';
                } else {
                    $this->attributes[$column] = null;
                }
            }
            // Si c'est déjà un array, l'encoder en JSON
            elseif (is_array($value)) {
                $this->attributes[$column] = json_encode($value);
            }
            // Si c'est une chaîne non vide qui n'est pas du JSON valide
            elseif (is_string($value) && !$this->isValidJson($value)) {
                // Tenter de convertir en array si ce n'est pas du JSON
                if (trim($value) !== '') {
                    $this->attributes[$column] = json_encode([$value]);
                } else {
                    $this->attributes[$column] = null;
                }
            }
            // Si c'est déjà du JSON valide, le laisser tel quel
        }
    }

    /**
     * Vérifier si une chaîne est du JSON valide
     */
    private function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function champs()
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet', 'projetable_id', 'champId')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }

    public function fiche_synthese()
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet', 'projetable_id', 'champId')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }

    /**
     * Get the projetable entity that the evaluation belongs to.
     */
    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentaireable');
    }

    /**
     * Get the projetable entity that the evaluation belongs to.
     */
    public function decisions()
    {
        return $this->morphMany(Decision::class, 'objet_decision');
    }

    /**
     * Get the projetable entity that the evaluation belongs to.
     */
    public function workflows()
    {
        return $this->morphMany(Workflow::class, 'projetable');
    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteurId')->where('type', "sous-secteur")->whereHas('parent', function ($query) {
            $query->where('type', 'secteur');
        });
    }

    public function ministere()
    {
        return $this->belongsTo(Organisation::class, 'ministereId')->where('type', "ministere")->whereNull("parentId");
    }

    public function categorie()
    {
        return $this->belongsTo(CategorieProjet::class, 'categorieId');
    }

    public function projet()
    {
        return $this->hasOne(Projet::class, 'ideeProjetId');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsableId');
    }

    public function demandeur()
    {
        return $this->morphTo('demandeur', 'demandeur_type', 'demandeurId');
    }

    public function financements()
    {
        return $this->morphToMany(Financement::class, 'projetable', 'sources_financement_projets', 'projetable_id', 'sourceId')
            ->withTimestamps();
    }

    public function sources_de_financement()
    {
        return $this->morphToMany(Financement::class, 'projetable', 'sources_financement_projets', 'projetable_id', 'sourceId')->where("type", "source")
            ->withTimestamps();
    }

    public function cibles()
    {
        return $this->morphToMany(Cible::class, 'projetable', 'cibles_projets', 'projetable_id', 'cibleId')
            ->withTimestamps();
    }

    public function odds()
    {
        return $this->morphToMany(Odd::class, 'projetable', 'odds_projets', 'projetable_id', 'oddId')
            ->withTimestamps();
    }

    public function typesIntervention()
    {
        return $this->morphToMany(TypeIntervention::class, 'projetable', 'types_intervention_projets', 'projetable_id', 'typeId')
            ->withTimestamps();
    }

    public function lieuxIntervention()
    {
        return $this->morphMany(LieuIntervention::class, 'projetable');
    }

    public function departements()
    {
        return $this->morphMany(LieuIntervention::class, 'projetable');
    }

    public function composants()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')
            ->withTimestamps();
    }

    public function orientations_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function ($query) {
            $query->where('slug', 'orientation-strategique-pnd');
        });
    }

    public function objectifs_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function ($query) {
            $query->where('slug', 'objectif-strategique-pnd');
        });
    }
    public function resultats_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function ($query) {
            $query->where('slug', 'resultats-strategique-pnd');
        });
    }

    public function axes_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function ($query) {
            $query->where('slug', 'axe-pag');
        });
    }

    public function actions_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function ($query) {
            $query->where('slug', 'action-pag');
        });
    }

    public function piliers_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function ($query) {
            $query->where('slug', 'pilier-pag');
        });
    }

    public function documents()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function evaluations()
    {
        return $this->morphMany(Evaluation::class, 'projetable');
    }

    public function evaluationsClimatique()
    {
        return $this->morphMany(Evaluation::class, 'projetable')->where("type_evaluation", "climatique");
    }

    public function evaluationsPertinence()
    {
        return $this->morphMany(Evaluation::class, 'projetable')->where("type_evaluation", "pertinence");
    }

    public function evaluationPertinence()
    {
        return $this->evaluationsPertinence()->latest('created_at');
        /* return $this->morphOne(Evaluation::class, 'projetable')->where("type", "pertinence")
                ->orderBy('created_at', 'desc')
                ->first(); */
        return $this->morphOne(Evaluation::class, 'projetable')
            ->where('type_evaluation', 'pertinence')
            ->latestOfMany(); // ✅ équivalent à orderBy('created_at', 'desc')->first()

    }

    public function evaluationsAMC()
    {
        return $this->morphMany(Evaluation::class, 'projetable')->where("type_evaluation", "amc");
    }

    /**
     * Récupérer le dernier rapport de faisabilité
     */
    public function evaluationClimatique()
    {
        return $this->evaluationsClimatique()->latest('created_at');
    }

    public function evaluationAMC()
    {
        return $this->evaluationsAMC()->latest('created_at');
    }

    /**
     * Relation pour charger l'historique de toutes les évaluations terminées
     * (pertinence, climatique, amc)
     */
    public function historiqueEvaluations(string $type = 'climatique'): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Evaluation::class, 'projetable')
            ->where('type_evaluation', $type)
            ->where('statut', 1)
            ->whereNotNull('date_fin_evaluation')
            ->whereHas("childEvaluations")
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relation pour charger l'historique de toutes les évaluations terminées
     * (climatique)
     */
    public function historiqueEvaluationsClimatique(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->historiqueEvaluations("climatique");
    }

    /**
     * Relation pour charger l'historique de toutes les évaluations terminées
     * (pertinence)
     */
    public function historiqueEvaluationsPertinence(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->historiqueEvaluations("pertinence");
    }

    /**
     * Relation pour charger l'historique de toutes les évaluations terminées
     * (pertinence)
     */
    public function historiqueEvaluationsAMC(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->historiqueEvaluations("amc");
    }

    /**
     * Relation pour charger l'historique de toutes les évaluations terminées
     * (pertinence)
     */
    public function historiqueValidationsPreliminaire(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->historiqueEvaluations("validation-idee-projet");
    }

    /**
     * Relation pour charger l'historique des validations par la dgpd
     * (pertinence)
     */
    public function historiqueValidations(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->historiqueEvaluations("validation-idee-projet-a-projet");
    }

    /**
     * Récupérer la dernière validation préliminaire
     */
    public function validationPreliminaire()
    {
        return $this->morphMany(Evaluation::class, 'projetable')
            ->where("type_evaluation", "validation-idee-projet")
            ->latest('created_at');
    }

    /**
     * Récupérer la dernière validation finale (DGPD)
     */
    public function validationFinale()
    {
        return $this->morphMany(Evaluation::class, 'projetable')
            ->where("type_evaluation", "validation-idee-projet-a-projet")
            ->latest('created_at');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id')
            ->active()
            ->ordered();
    }

    public function allFichiers()
    {
        return $this->morphMany(Fichier::class, 'fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id');
    }

    public function fichiersParCategorie(string $categorie)
    {
        return $this->fichiers()->byCategorie($categorie);
    }

    public function scopeEvaluateursClimatique()
    {
        return User::byMinistere($this->ministere->id);
    }


    /**
     * Extraire toutes les relations des données de champs
     */
    protected function relationshipChamps(): array
    {
        return [
            'secteurId' => 'secteur',
            /*'grand_secteur' =>  Secteur::class,
            'secteur' =>  Secteur::class,*/
            'categorieId' => 'categorie',


            'orientations_strategiques' => 'orientations_strategique_png',
            'resultats_strategiques' => 'resultats_strategique_png',
            'objectifs_strategiques' => 'objectifs_strategique_png',
            'piliers_pag' => 'piliers_pag',
            'axes_pag' => 'axes_pag',
            'actions_pag' => 'actions_pag',

            'cibles' => 'cibles',
            'odds' => 'odds',
            'sources_financement' => 'sources_de_financement',
            /*'natures_financement' => Financement::class,
            'types_financement' => Financement::class,*/

            // dans lieuxIntervention
            'departements' => 'lieuxIntervention', // dans lieuxIntervention est disponible via la cle departementId
            'communes' => 'lieuxIntervention', // dans lieuxIntervention est disponible via la cle communeId
            'arrondissements' => 'lieuxIntervention', // dans lieuxIntervention est disponible via la cle arrondissementId
            'villages' => 'lieuxIntervention', // dans lieuxIntervention est disponible via la cle villageId
        ];
    }

    /**
     * Retourner les champs formatés avec les hashed_ids pour les relations
     */
    public function getFormattedChamps()
    {
        return $this->champs->map(function ($champ) {
            $value = $champ->pivot->valeur;
            $attribut = $champ->attribut;

            // Utiliser le mapping relationshipChamps()
            $relationMappings = $this->relationshipChamps();

            // Si c'est un champ relationnel, convertir l'ID en hashed_id
            if (isset($relationMappings[$attribut]) && $value) {
                $mapping = $relationMappings[$attribut];

                // Cas 1: Si c'est une classe de modèle directe (ex: Secteur::class, Financement::class)
                if (is_string($mapping) && class_exists($mapping)) {
                    $entity = $mapping::find($value);
                    $value = $entity ? $entity->hashed_id : $value;
                }
                // Cas 2: Cas spécial pour lieuxIntervention (retourne un tableau)
                elseif ($mapping === 'lieuxIntervention') {
                    // Charger la relation si nécessaire
                    if (!$this->relationLoaded('lieuxIntervention')) {
                        $this->load('lieuxIntervention');
                    }

                    $lieuMapping = [
                        'departements' => ['column' => 'departementId', 'model' => Departement::class],
                        'communes' => ['column' => 'communeId', 'model' => Commune::class],
                        'arrondissements' => ['column' => 'arrondissementId', 'model' => Arrondissement::class],
                        'villages' => ['column' => 'villageId', 'model' => Village::class],
                    ];

                    if (isset($lieuMapping[$attribut])) {
                        $columnName = $lieuMapping[$attribut]['column'];
                        $modelClass = $lieuMapping[$attribut]['model'];

                        // Récupérer tous les IDs de tous les lieuxIntervention
                        $hashedIds = $this->lieuxIntervention
                            ->pluck($columnName)
                            ->filter() // Enlever les valeurs nulles
                            ->unique()
                            ->map(function ($relatedId) use ($modelClass) {
                                $entity = $modelClass::find($relatedId);
                                return $entity ? $entity->hashed_id : $relatedId;
                            })
                            ->values()
                            ->toArray();

                        $value = $hashedIds;
                    }
                }
                // Cas 3: Relations standard (belongsTo ou many-to-many)
                else {
                    // Vérifier que la méthode de relation existe
                    if (method_exists($this, $mapping)) {
                        // Charger la relation si nécessaire
                        if (!$this->relationLoaded($mapping)) {
                            $this->load($mapping);
                        }

                        $related = $this->$mapping;

                        // Si c'est une Collection (many-to-many), convertir le tableau d'IDs en tableau de hashed_ids
                        if (is_a($related, \Illuminate\Database\Eloquent\Collection::class)) {
                            // $value peut être une string JSON, la décoder si nécessaire
                            if (is_string($value)) {
                                $decoded = json_decode($value, true);
                                // Vérifier que c'est du JSON valide et que c'est un tableau
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $value = $decoded;
                                }
                            }

                            // $value contient un tableau d'IDs, convertir en hashed_ids
                            if (is_array($value)) {
                                // Déterminer la classe du modèle lié à partir de la relation
                                $modelClass = null;
                                try {
                                    $relation = $this->$mapping();
                                    if (method_exists($relation, 'getRelated')) {
                                        $modelClass = get_class($relation->getRelated());
                                    }
                                } catch (\Exception $e) {
                                    // Si on ne peut pas obtenir le modèle depuis la relation, essayer depuis la collection
                                    if ($related->isNotEmpty()) {
                                        $modelClass = get_class($related->first());
                                    }
                                }

                                $value = collect($value)->map(function ($id) use ($related, $modelClass) {
                                    $entity = $related->firstWhere('id', $id);

                                    // Si l'entité n'est pas trouvée dans la collection chargée, la chercher en base
                                    if (!$entity && $modelClass) {
                                        $entity = $modelClass::find($id);
                                    }

                                    return $entity ? $entity->hashed_id : $id;
                                })->toArray();
                            }
                        }
                        // Si c'est une relation belongsTo simple
                        elseif ($related) {
                            $value = $related->hashed_id;
                        }
                    }
                }
            }

            return [
                'id' => $champ->hashed_id,
                'attribut' => $attribut,
                'value' => $value,
                'pivot_id' => $champ->pivot->hashed_id
            ];
        });
    }

    /**
     * Remonte jusqu'au TypeProgramme racine (programme)
     *
     * @param \App\Models\TypeProgramme|null $typeProgramme
     * @return \App\Models\TypeProgramme|null
     */
    private function getProgrammeRacine($typeProgramme)
    {
        if (!$typeProgramme) {
            return null;
        }

        // Remonter jusqu'au programme racine (celui sans parent)
        while ($typeProgramme->parent) {
            $typeProgramme = $typeProgramme->parent;
        }

        return $typeProgramme;
    }

    /**
     * Construit la hiérarchie descendante des TypeProgramme avec leurs composants
     *
     * @param \App\Models\TypeProgramme $typeProgramme
     * @return array
     */
    private function buildProgrammeHierarchieDescendante($typeProgramme)
    {
        // Charger les composants associés à ce TypeProgramme
        $composants = $typeProgramme->composantsProgramme->map(function ($composant) {
            return [
                'id' => $composant->hashed_id,
                'intitule' => $composant->intitule,
                //'indice' => $composant->indice,
            ];
        });

        // Charger les enfants (sous-types) avec leurs composants
        $children = $typeProgramme->children->map(function ($child) {
            return $this->buildProgrammeHierarchieDescendante($child);
        });

        return [
            'id' => $typeProgramme->hashed_id,
            'type_programme' => $typeProgramme->type_programme,
            'slug' => $typeProgramme->slug,
            'type' => $typeProgramme->parent ? "composant-programme" : "programme",
            'composants_data' => $composants,
            'composants' => $children->isEmpty() ? [] : $children,
        ];
    }

    /**
     * Retourne la hiérarchie complète des programmes depuis les composants
     * Remonte jusqu'au programme racine puis redescend avec tous les composants
     *
     * @return \Illuminate\Support\Collection
     */
    public function programmes()
    {
        // Récupérer tous les programmes racines uniques depuis les composants
        $programmesRacines = $this->composants->map(function ($composant) {
            return $this->getProgrammeRacine($composant->typeProgramme);
        })->filter()->unique('id');

        // Pour chaque programme racine, construire la hiérarchie descendante complète
        return $programmesRacines->map(function ($programmeRacine) {
            return $this->buildProgrammeHierarchieDescendante($programmeRacine);
        });
    }

    /**
     * Remonte jusqu'au Financement racine (type)
     *
     * @param \App\Models\Financement|null $financement
     * @return \App\Models\Financement|null
     */
    private function getFinancementRacine($financement)
    {
        if (!$financement) {
            return null;
        }

        // Remonter jusqu'au type racine (celui sans parent)
        while ($financement->parent) {
            $financement = $financement->parent;
        }

        return $financement;
    }

    /**
     * Construit la hiérarchie descendante des Financements
     *
     * @param \App\Models\Financement $financement
     * @return array
     */
    private function buildFinancementHierarchieDescendante($financement)
    {
        // Charger les enfants (natures ou sources) récursivement
        $children = $financement->children->map(function ($child) {
            return $this->buildFinancementHierarchieDescendante($child);
        });

        return [
            'id' => $financement->hashed_id,
            'nom' => $financement->nom,
            'nom_usuel' => $financement->nom_usuel,
            'slug' => $financement->slug,
            'type' => $financement->type?->value ?? $financement->type,
            'enfants' => $children->isEmpty() ? [] : $children,
        ];
    }

    /**
     * Retourne la hiérarchie complète des financements depuis les sources
     * Remonte jusqu'au type racine puis redescend avec toutes les natures et sources
     *
     * @return \Illuminate\Support\Collection
     */
    public function types_financement()
    {
        // Récupérer tous les types racines uniques depuis les sources de financement
        $typesRacines = $this->sources_de_financement->map(function ($source) {
            return $this->getFinancementRacine($source);
        })->filter()->unique('id');

        // Pour chaque type racine, construire la hiérarchie descendante complète
        return $typesRacines->map(function ($typeRacine) {
            return $this->buildFinancementHierarchieDescendante($typeRacine);
        });
    }

    /**
     * Obtenir les fichiers appreciation tdr
     */
    public function ficheIdeeProjet(): MorphOne
    {
        return $this->morphOne(
            Fichier::class,
            'fichierAttachable',
            'fichier_attachable_type',
            'fichier_attachable_id'
        )
            ->byCategorie('fiche_idee_projet')
            ->latestOfMany();
        return $this->fichiers()->orderBy("created_at", "desc")->byCategorie('fiche_idee_projet');
    }

    /**
     * Obtenir les fichiers appreciation tdr
     */
    public function evaluationPertinenceExporter(): MorphOne
    {
        return $this->morphOne(
            Fichier::class,
            'fichierAttachable',
            'fichier_attachable_type',
            'fichier_attachable_id'
        )
            ->byCategorie('evaluation_pertinence')->orderBy("created_at", "desc");
    }


    /**
     * Obtenir les fichiers appreciation tdr
     */
    public function evaluationClimatiqueExporter(): MorphOne
    {
        return $this->morphOne(
            Fichier::class,
            'fichierAttachable',
            'fichier_attachable_type',
            'fichier_attachable_id'
        )
            ->byCategorie('evaluation_climatique')->orderBy("created_at", "desc");
    }


    /**
     * Obtenir les fichiers appreciation tdr
     */
    public function AMCExporter(): MorphOne
    {
        return $this->morphOne(
            Fichier::class,
            'fichierAttachable',
            'fichier_attachable_type',
            'fichier_attachable_id'
        )
            ->byCategorie('evaluation_amc')->orderBy("created_at", "desc");
    }
}
