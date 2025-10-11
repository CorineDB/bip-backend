<?php

namespace App\Models;

use App\Enums\PhasesIdee;
use App\Enums\SousPhaseIdee;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Projet extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projets';

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
        'ideeProjetId',
        'identifiant_bip',
        'identifiant_sigfp',
        'ficheIdee',
        'statut',
        'phase',
        'sous_phase',
        'decision',
        'sigle',
        'type_projet',
        'parties_prenantes',
        'objectifs_specifiques',
        'resultats_attendus',
        'isdeleted',
        'body_projet',
        'cout_estimatif_projet',
        'cout_dollar_americain',
        'cout_euro',
        'date_debut_etude',
        'date_fin_etude',
        'date_prevue_demarrage',
        'date_effective_demarrage',
        'cout_dollar_canadien',
        'risques_immediats',
        'sommaire',
        'objectif_general',
        'conclusions',
        'description',
        'constats_majeurs',
        'public_cible',
        'estimation_couts',
        'description_decision',
        'impact_environnement',
        'aspect_organisationnel',
        'description_extrants',
        'caracteristiques',
        'score_climatique',
        'score_amc',
        'score_pertinence',
        'duree',
        'description_projet',
        'origine',
        'situation_desiree',
        'situation_actuelle',
        'contraintes',
        'echeancier',
        'fondement',
        'secteurId',
        'ministereId',
        'categorieId',
        'responsableId',
        'demandeurId',
        'demandeur_type',
        'titre_projet',
        'porteur_projet',
        'est_ancien',
        'resume_tdr_prefaisabilite',
        'resume_tdr_faisabilite',
        'info_cabinet_etude_prefaisabilite',
        'info_cabinet_etude_faisabilite',
        'est_a_haut_risque',
        'est_dur',
        'mesures_adaptation',
        'info_etude_prefaisabilite',
        'info_etude_faisabilite',
        'canevas_appreciation_pertinence',
        'canevas_climatique',
        'canevas_amc',
        'investissement_initial',
        'van',
        'tri',
        'flux_tresorerie',
        'duree_vie',
        'taux_actualisation'
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
        'decision' => 'array',
        'est_a_haut_risque' => 'boolean',
        'est_ancien'=>'boolean',
        'est_dur' => 'boolean',
        'cout_estimatif_projet' => 'array',
        'ficheIdee' => 'array',
        'info_etude_prefaisabilite' => 'array',
        'info_etude_faisabilite' => 'array',
        'parties_prenantes' => 'array',
        'objectifs_specifiques' => 'array',
        'resultats_attendus' => 'array',
        'info_cabinet_etude_prefaisabilite' => 'array',
        'info_cabinet_etude_faisabilite' => 'array',
        'body_projet' => 'array',
        'mesures_adaptation' => 'array',
        'canevas_appreciation_pertinence' => 'array',
        'canevas_climatique' => 'array',
        'canevas_amc' => 'array',
        'flux_tresorerie' => 'array',
        'investissement_initial' => 'float',
        'van' => 'float',
        'tri' => 'float',
        'duree_vie' => 'integer',
        'taux_actualisation' => 'float',
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
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->update([
                // Exemple : 'nom' => time() . '::' . $model->nom,
            ]);

            if (method_exists($model, 'user')) {
                // Exemple : $model->user()->delete();
            }
        });

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
            'decision', 'cout_estimatif_projet', 'ficheIdee',
            'parties_prenantes', 'objectifs_specifiques',
            'resultats_attendus', 'body_projet'
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

    // Relations

    public function ideeProjet()
    {
        return $this->belongsTo(IdeeProjet::class, 'ideeProjetId');
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

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentaireable');
    }

    public function decisions()
    {
        return $this->morphMany(Decision::class, 'objet_decision');
    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteurId')->where('type', "sous-secteur")->whereHas('parent', function($query){
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

    public function composants()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')
            ->withTimestamps();
    }

    public function orientations_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'orientation-strategique-pnd');
        });
    }

    public function objectifs_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'objectif-strategique-pnd');
        });
    }

    public function resultats_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'resultats-strategique-pnd');
        });
    }

    public function axes_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'axe-pag');
        });
    }

    public function actions_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'action-pag');
        });
    }

    public function pilliers_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
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

    public function workflows()
    {
        return $this->morphMany(Workflow::class, 'projetable');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id')
            ->active()
            ->ordered();
    }

    public function evaluationsClimatique()
    {
        return $this->morphMany(Evaluation::class, 'projetable')->where("type_evaluation", "climatique");
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

    public function tdrs_prefaisabilite()
    {
        return $this->fichiers()->where('categorie', 'tdr-prefaisabilite');
    }

    public function tdrs_faisabilite()
    {
        return $this->fichiers()->where('categorie', 'tdr-faisabilite');
    }

    // Les fichiers de rapports sont maintenant gérés via la table 'rapports'
    // Utilisez les relations rapportPrefaisabilite(), rapportFaisabilite(), rapportEvaluationExAnte()
    // puis ->fichiers() pour accéder aux fichiers associés aux rapports

    // Nouvelles relations pour la table rapports avec checklists
    public function rapports()
    {
        return $this->hasMany(Rapport::class);
    }

    /**
     * Récupérer le dernier rapport de préfaisabilité
     */
    public function rapportPrefaisabilite()
    {
        return $this->rapports()->prefaisabilite()->latest('created_at');
    }

    /**
     * Récupérer tous les rapports de préfaisabilité
     */
    public function rapportsPrefaisabilite()
    {
        return $this->rapports()->prefaisabilite()->orderBy('created_at');
    }

    /**
     * Récupérer le dernier rapport de faisabilité
     */
    public function rapportFaisabilite()
    {
        return $this->rapports()->faisabilite()->latest('created_at');
    }

    /**
     * Récupérer tous les rapports de faisabilité
     */
    public function rapportsFaisabilite()
    {
        return $this->rapports()->faisabilite()->orderBy('created_at');
    }

    /**
     * Récupérer le dernier rapport d'évaluation ex-ante
     */
    public function rapportEvaluationExAnte()
    {
        return $this->rapports()->evaluationExAnte()->latest('created_at');
    }

    /**
     * Récupérer tous les rapports d'évaluation ex-ante
     */
    public function rapportsEvaluationExAnte()
    {
        return $this->rapports()->evaluationExAnte()->orderBy('created_at');
    }

    // Les documents annexes des rapports sont maintenant gérés via la table 'rapports'
    // Utilisez $rapport->documentsAnnexes() pour accéder aux documents annexes d'un rapport

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

    // Relations avec les TDRs

    /**
     * Relation avec tous les TDRs du projet
     */
    public function noteConceptuelle()
    {
        return $this->hasOne(NoteConceptuelle::class, 'projetId')->orderBy("created_at", "desc");
    }

    /**
     * Relation avec tous les TDRs du projet
     */
    public function notes_conceptuelle()
    {
        return $this->hasMany(NoteConceptuelle::class, 'projetId')->orderBy("created_at", "desc");
    }

    // Relations avec les TDRs

    /**
     * Relation avec tous les TDRs du projet
     */
    public function tdrs()
    {
        return $this->hasMany(Tdr::class, 'projet_id');
    }

    /**
     * TDRs de préfaisabilité
     */
    public function tdrsPrefaisabilite()
    {
        return $this->tdrs()->prefaisabilite();
    }

    /**
     * TDRs de faisabilité
     */
    public function tdrsFaisabilite()
    {
        return $this->tdrs()->faisabilite();
    }

    /**
     * Rapports d'évaluation ex-ante (alias pour compatibilité)
     */
    public function rapports_evaluation_ex_ante()
    {
        return $this->rapports()->evaluationExAnte();
    }

    /**
     * Documents annexes des rapports d'évaluation ex-ante
     */
    public function documents_annexe_rapports_evaluation_ex_ante()
    {
        return $this->fichiersParCategorie('annexe_rapport_evaluation_ex_ante');
    }

    /**
     * TDR de préfaisabilité actif (le plus récent avec statut valide ou en cours)
     */
    public function tdrPrefaisabiliteActif()
    {
        return $this->tdrsPrefaisabilite()
            ->whereIn('statut', ['brouillon', 'soumis', 'en_evaluation', 'valide', 'retour_travail_supplementaire'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * TDR de faisabilité actif (le plus récent avec statut valide ou en cours)
     */
    public function tdrFaisabiliteActif()
    {
        return $this->tdrsFaisabilite()
            ->whereIn('statut', [
                'brouillon','soumis', 'en_evaluation', 'valide', 'retour_travail_supplementaire'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * TDR préfaisabilité avec détails complets
     */
    public function tdrPrefaisabilite()
    {
        return $this->tdrsPrefaisabilite()
            ->whereIn('statut', [
                'brouillon',
                'soumis',
                'en_evaluation',
                'valide',
                'retour_travail_supplementaire'
            ])
            ->with([
                'soumisPar',
                'redigerPar',
                'fichiers.uploadedBy'
            ])
            ->orderByDesc('created_at');
        return $this->tdrPrefaisabiliteActif()
            ->with(['soumisPar', 'redigerPar', 'fichiers.uploadedBy'])->first();
    }

    /**
     * TDR faisabilité avec détails complets
     */
    public function tdrFaisabilite()
    {
        return $this->tdrsFaisabilite()
            ->whereIn('statut', [
                'brouillon',
                'soumis',
                'en_evaluation',
                'valide',
                'retour_travail_supplementaire'
            ])
            ->with([
                'soumisPar',
                'redigerPar',
                'fichiers.uploadedBy'
            ])
            ->orderByDesc('created_at');
        return $this->tdrFaisabiliteActif()
            ->with(['soumisPar', 'redigerPar', 'fichiers.uploadedBy'])->first();
    }

    // Méthodes pour récupérer les TDRs avec historique

    /**
     * Obtenir le TDR de préfaisabilité actif avec son historique complet
     */
    public function getTdrPrefaisabiliteAvecHistorique()
    {
        return $this->tdrPrefaisabiliteActif()
            ->with([
                'fichiers' => function($q) { $q->active()->ordered(); },
                'commentaires' => function($q) { $q->orderBy('created_at', 'desc'); },
                'commentaires.commentateur:id,name,email',
                'soumisPar:id,name,email',
                'evaluateur:id,name,email',
                'validateur:id,name,email',
                'decideur:id,name,email'
            ])
            ->first();
    }

    /**
     * Obtenir le TDR de faisabilité actif avec son historique complet
     */
    public function getTdrFaisabiliteAvecHistorique()
    {
        return $this->tdrFaisabiliteActif()
            ->with([
                'fichiers' => function($q) { $q->active()->ordered(); },
                'commentaires' => function($q) { $q->orderBy('created_at', 'desc'); },
                'commentaires.commentateur:id,name,email',
                'soumisPar:id,name,email',
                'evaluateur:id,name,email',
                'validateur:id,name,email',
                'decideur:id,name,email'
            ])
            ->first();
    }

    /**
     * Obtenir l'historique complet de tous les TDRs du projet
     */
    public function getHistoriqueTdrs()
    {
        return $this->tdrs()
            ->with([
                'fichiers' => function($q) { $q->active()->ordered(); },
                'commentaires' => function($q) { $q->orderBy('created_at', 'desc'); },
                'commentaires.commentateur:id,name,email',
                'soumisPar:id,name,email',
                'evaluateur:id,name,email',
                'validateur:id,name,email',
                'decideur:id,name,email'
            ])
            ->orderBy('type')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtenir les commentaires d'évaluation de tous les TDRs
     */
    public function getCommentairesEvaluationTdrs()
    {
        return \App\Models\Commentaire::whereIn('commentaireable_id',
                $this->tdrs()->pluck('id')
            )
            ->where('commentaireable_type', Tdr::class)
            ->with(['commentateur:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('commentaireable_id');
    }

    /**
     * Vérifier si le projet a un TDR de préfaisabilité valide
     */
    public function hasTdrPrefaisabiliteValide(): bool
    {
        return $this->tdrsPrefaisabilite()
            ->where('statut', 'valide')
            ->exists();
    }

    /**
     * Vérifier si le projet a un TDR de faisabilité valide
     */
    public function hasTdrFaisabiliteValide(): bool
    {
        return $this->tdrsFaisabilite()
            ->where('statut', 'valide')
            ->exists();
    }

    /**
     * Obtenir le statut du workflow TDR pour le projet
     */
    public function getStatutWorkflowTdr(): array
    {
        $tdrPrefaisabilite = $this->getTdrPrefaisabiliteAvecHistorique();
        $tdrFaisabilite = $this->getTdrFaisabiliteAvecHistorique();

        return [
            'prefaisabilite' => [
                'existe' => !is_null($tdrPrefaisabilite),
                'statut' => $tdrPrefaisabilite?->statut,
                'decision_finale' => $tdrPrefaisabilite?->decision_finale,
                'date_soumission' => $tdrPrefaisabilite?->date_soumission,
                'date_evaluation' => $tdrPrefaisabilite?->date_evaluation,
                'date_validation' => $tdrPrefaisabilite?->date_validation,
                'nombre_commentaires' => $tdrPrefaisabilite?->commentaires?->count() ?? 0,
                'nombre_fichiers' => $tdrPrefaisabilite?->fichiers?->count() ?? 0
            ],
            'faisabilite' => [
                'existe' => !is_null($tdrFaisabilite),
                'statut' => $tdrFaisabilite?->statut,
                'decision_finale' => $tdrFaisabilite?->decision_finale,
                'date_soumission' => $tdrFaisabilite?->date_soumission,
                'date_evaluation' => $tdrFaisabilite?->date_evaluation,
                'date_validation' => $tdrFaisabilite?->date_validation,
                'nombre_commentaires' => $tdrFaisabilite?->commentaires?->count() ?? 0,
                'nombre_fichiers' => $tdrFaisabilite?->fichiers?->count() ?? 0
            ],
            'peut_soumettre_faisabilite' => $this->hasTdrPrefaisabiliteValide(),
            'workflow_complet' => $this->hasTdrPrefaisabiliteValide() && $this->hasTdrFaisabiliteValide()
        ];
    }

    /**
     * Calcule la Valeur Actuelle Nette (VAN) du projet.
     *
     * @return float|null La VAN du projet, ou null si les données sont insuffisantes.
     */
    public function calculerVAN(): ?float
    {
        // Utiliser le taux d'actualisation du projet, ou 10% par défaut.
        $tauxActualisation = (float) ($this->taux_actualisation ?? 0.1);

        // I0: L'investissement initial
        $investissementInitial = (float) $this->investissement_initial;

        // CFt: Les flux de trésorerie nets (automatiquement casté en array par Eloquent)
        $fluxTresorerie = $this->flux_tresorerie;

        if (!is_array($fluxTresorerie) || empty($fluxTresorerie)) {
            return null; // Pas de flux de trésorerie pour calculer la VAN
        }

        $van = 0;
        foreach ($fluxTresorerie as $flux) {
            // Vérifier que les clés 't' et 'CFt' existent
            if (isset($flux['t']) && isset($flux['CFt'])) {
                $t = (int) $flux['t'];
                $cft = (float) $flux['CFt'];
                $van += $cft / pow(1 + $tauxActualisation, $t);
            }
        }

        return $van - $investissementInitial;
    }

    /**
     * Calcule le Taux de Rentabilité Interne (TRI) du projet.
     *
     * Le TRI est le taux d'actualisation qui annule la VAN du projet.
     * Cette méthode utilise une approche itérative (méthode de Newton-Raphson) pour trouver le TRI.
     *
     * @return float|null Le TRI en format décimal (ex: 0.15 pour 15%), ou null si le calcul échoue.
     */
    public function calculerTRI(): ?float
    {
        // Paramètres de l'algorithme numérique
        $estimationInitiale = 0.1; // 10%
        $maxIterations = 100;
        $precision = 1e-5;

        // Prépare le tableau des flux de trésorerie : [-I0, CF1, CF2, ...]
        $investissementInitial = (float) $this->investissement_initial;
        $fluxTresorerie = $this->flux_tresorerie; // Casté en array par Eloquent

        if ($investissementInitial <= 0 || !is_array($fluxTresorerie) || empty($fluxTresorerie)) {
            return null; // Données insuffisantes pour le calcul
        }

        // Le premier flux (t=0) est l'investissement initial (négatif)
        $cashFlows = [-$investissementInitial];
        foreach ($fluxTresorerie as $flux) {
            if (isset($flux['CFt'])) {
                $cashFlows[] = (float) $flux['CFt'];
            }
        }

        // Implémentation de la méthode de Newton-Raphson pour trouver la racine.
        $tri = $estimationInitiale;

        for ($i = 0; $i < $maxIterations; $i++) {
            $van = 0;
            $deriveeVan = 0;

            foreach ($cashFlows as $t => $cf) {
                if ((1 + $tri) == 0 && $t > 0) return null; // Evite la division par zéro
                if ((1 + $tri) != 0) {
                    $van += $cf / pow(1 + $tri, $t);
                    if ($t > 0) {
                        $deriveeVan -= $t * $cf / pow(1 + $tri, $t + 1);
                    }
                }
            }

            if (abs($van) < $precision) {
                return $tri; // Solution trouvée avec la précision souhaitée
            }

            // Éviter la division par zéro pour la dérivée
            if ($deriveeVan == 0) {
                return null; // Le calcul ne peut pas continuer
            }

            // Prochaine itération de Newton-Raphson
            $tri = $tri - $van / $deriveeVan;
        }

        return null; // Pas de convergence après le nombre maximum d'itérations
    }
}
