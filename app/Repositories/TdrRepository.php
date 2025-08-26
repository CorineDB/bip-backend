<?php

namespace App\Repositories;

use App\Models\Tdr;
use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\Contracts\TdrRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TdrRepository extends BaseRepository implements TdrRepositoryInterface
{
    public function __construct(Tdr $tdr)
    {
        parent::__construct($tdr);
    }

    /**
     * Obtenir les TDRs d'un projet spécifique
     */
    public function getByProjetId(int $projetId): Collection
    {
        return $this->model->forProjet($projetId)
            ->with(['soumisPar', 'evaluateur', 'validateur', 'decideur', 'fichiers', 'commentaires.commentateur'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtenir un TDR spécifique d'un projet par type
     */
    public function findByProjetAndType(int $projetId, string $type): ?Tdr
    {
        return $this->model->forProjet($projetId)
            ->ofType($type)
            ->with(['soumisPar', 'evaluateur', 'validateur', 'decideur', 'fichiers', 'commentaires.commentateur'])
            ->first();
    }

    /**
     * Obtenir les TDRs par statut
     */
    public function getByStatut(string $statut): Collection
    {
        return $this->model->withStatus($statut)
            ->with(['projet', 'soumisPar'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtenir les TDRs par type (prefaisabilite/faisabilite)
     */
    public function getByType(string $type): Collection
    {
        return $this->model->ofType($type)
            ->with(['projet', 'soumisPar'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtenir les TDRs en attente d'évaluation
     */
    public function getEnAttenteEvaluation(): Collection
    {
        return $this->model->soumis()
            ->with(['projet', 'soumisPar'])
            ->orderBy('date_soumission', 'asc')
            ->get();
    }

    /**
     * Obtenir les TDRs évalués par un utilisateur
     */
    public function getEvaluesParUtilisateur(int $evaluateurId): Collection
    {
        return $this->model->evaluatedBy($evaluateurId)
            ->with(['projet', 'soumisPar'])
            ->orderBy('date_evaluation', 'desc')
            ->get();
    }

    /**
     * Obtenir les TDRs soumis entre deux dates
     */
    public function getSoumisBetween($dateDebut, $dateFin): Collection
    {
        $debut = Carbon::parse($dateDebut);
        $fin = Carbon::parse($dateFin);

        return $this->model->soumisEntre($debut, $fin)
            ->with(['projet', 'soumisPar'])
            ->orderBy('date_soumission', 'desc')
            ->get();
    }

    /**
     * Rechercher des TDRs avec filtres
     */
    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Appliquer les filtres
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (isset($filters['statut']) && !empty($filters['statut'])) {
            $query->withStatus($filters['statut']);
        }

        if (isset($filters['projet_id']) && !empty($filters['projet_id'])) {
            $query->forProjet($filters['projet_id']);
        }

        if (isset($filters['evaluateur_id']) && !empty($filters['evaluateur_id'])) {
            $query->evaluatedBy($filters['evaluateur_id']);
        }

        if (isset($filters['decision_finale']) && !empty($filters['decision_finale'])) {
            $query->withDecisionFinale($filters['decision_finale']);
        }

        if (isset($filters['date_debut']) && isset($filters['date_fin'])) {
            $debut = Carbon::parse($filters['date_debut']);
            $fin = Carbon::parse($filters['date_fin']);
            $query->soumisEntre($debut, $fin);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Charger les relations
        $query->with([
            'projet:id,titre_projet,identifiant_bip',
            'soumisPar:id,name,email',
            'evaluateur:id,name,email',
            'validateur:id,name,email',
            'decideur:id,name,email',
            'fichiers' => function($q) { $q->select('id', 'nom_original', 'categorie', 'taille', 'fichier_attachable_id', 'fichier_attachable_type'); },
            'commentaires' => function($q) { $q->select('id', 'commentaire', 'date', 'commentaireable_id', 'commentaireable_type', 'commentateurId')->latest()->limit(5); },
            'commentaires.commentateur:id,name'
        ]);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Mettre à jour le statut d'un TDR
     */
    public function updateStatut(int $id, string $statut): bool
    {
        return $this->model->where('id', $id)->update(['statut' => $statut]);
    }

    /**
     * Mettre à jour les statistiques d'évaluation
     */
    public function updateStatistiques(int $id, int $passe, int $retour, int $nonAccepte): bool
    {
        return $this->model->where('id', $id)->update([
            'nombre_passe' => $passe,
            'nombre_retour' => $retour,
            'nombre_non_accepte' => $nonAccepte
        ]);
    }

    /**
     * Obtenir les TDRs avec leurs relations
     */
    public function getWithRelations(array $relations = []): Collection
    {
        $defaultRelations = [
            'projet:id,titre_projet,identifiant_bip',
            'soumisPar:id,name,email',
            'evaluateur:id,name,email',
            'validateur:id,name,email',
            'decideur:id,name,email',
            'fichiers',
            'commentaires.commentateur:id,name'
        ];

        $relations = !empty($relations) ? $relations : $defaultRelations;

        return $this->model->with($relations)->get();
    }

    /**
     * Obtenir les statistiques globales des TDRs
     */
    public function getStatistiques(): array
    {
        $total = $this->model->count();

        return [
            'total' => $total,
            'par_type' => [
                'prefaisabilite' => $this->model->prefaisabilite()->count(),
                'faisabilite' => $this->model->faisabilite()->count(),
            ],
            'par_statut' => [
                'brouillon' => $this->model->withStatus('brouillon')->count(),
                'soumis' => $this->model->soumis()->count(),
                'en_evaluation' => $this->model->enEvaluation()->count(),
                'valide' => $this->model->valides()->count(),
                'retour_travail_supplementaire' => $this->model->withStatus('retour_travail_supplementaire')->count(),
                'abandonne' => $this->model->withStatus('abandonne')->count(),
            ],
            'par_decision_finale' => [
                'passe' => $this->model->withDecisionFinale('passe')->count(),
                'retour_travail_supplementaire' => $this->model->withDecisionFinale('retour_travail_supplementaire')->count(),
                'abandonne' => $this->model->withDecisionFinale('abandonne')->count(),
            ],
            'moyennes' => [
                'pourcentage_passe' => $this->model->avg('nombre_passe'),
                'pourcentage_retour' => $this->model->avg('nombre_retour'),
                'pourcentage_non_accepte' => $this->model->avg('nombre_non_accepte'),
            ],
            'dates' => [
                'dernier_soumis' => $this->model->whereNotNull('date_soumission')
                    ->orderBy('date_soumission', 'desc')
                    ->value('date_soumission'),
                'derniere_evaluation' => $this->model->whereNotNull('date_evaluation')
                    ->orderBy('date_evaluation', 'desc')
                    ->value('date_evaluation'),
            ]
        ];
    }

    /**
     * Obtenir les TDRs avec fichiers
     */
    public function getAvecFichiers(): Collection
    {
        return $this->model->has('fichiers')
            ->with(['projet', 'fichiers', 'soumisPar'])
            ->get();
    }

    /**
     * Obtenir les TDRs avec commentaires récents
     */
    public function getAvecCommentairesRecents(int $jours = 7): Collection
    {
        return $this->model->whereHas('commentaires', function($query) use ($jours) {
                $query->where('created_at', '>=', now()->subDays($jours));
            })
            ->with(['projet', 'commentaires' => function($q) use ($jours) {
                $q->recents($jours)->with('commentateur');
            }])
            ->get();
    }

    /**
     * Attacher un fichier à un TDR
     */
    public function attacherFichier(int $tdrId, array $fichierData): bool
    {
        $tdr = $this->find($tdrId);
        if (!$tdr) {
            return false;
        }

        $tdr->fichiers()->create($fichierData);
        return true;
    }

    /**
     * Ajouter un commentaire à un TDR
     */
    public function ajouterCommentaire(int $tdrId, string $commentaire, int $commentateurId): bool
    {
        $tdr = $this->find($tdrId);
        if (!$tdr) {
            return false;
        }

        $tdr->ajouterCommentaire($commentaire, $commentateurId);
        return true;
    }
}