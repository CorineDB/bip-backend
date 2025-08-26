<?php

namespace App\Repositories;

use App\Models\Projet;
use App\Models\Tdr;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class ProjetRepository extends BaseRepository implements ProjetRepositoryInterface
{
    public function __construct(Projet $model)
    {
        parent::__construct($model);
    }

    /**
     * Obtenir un projet avec ses TDRs actifs et historique
     */
    public function getProjetAvecTdrs(int $projetId): ?Projet
    {
        return $this->model->with([
            'tdrs' => function($query) {
                $query->with([
                    'fichiers' => function($q) { $q->active()->ordered(); },
                    'commentaires' => function($q) { $q->orderBy('created_at', 'desc'); },
                    'commentaires.commentateur:id,name,email',
                    'soumisPar:id,name,email',
                    'evaluateur:id,name,email',
                    'validateur:id,name,email',
                    'decideur:id,name,email'
                ])->orderBy('type')->orderBy('created_at', 'desc');
            }
        ])->find($projetId);
    }

    /**
     * Obtenir un projet avec le TDR de préfaisabilité actif
     */
    public function getProjetAvecTdrPrefaisabilite(int $projetId): ?Projet
    {
        return $this->model->with([
            'tdrsPrefaisabilite' => function($query) {
                $query->whereIn('statut', ['soumis', 'en_evaluation', 'valide', 'retour_travail_supplementaire'])
                    ->with([
                        'fichiers' => function($q) { $q->active()->ordered(); },
                        'commentaires' => function($q) { $q->orderBy('created_at', 'desc'); },
                        'commentaires.commentateur:id,name,email',
                        'soumisPar:id,name,email',
                        'evaluateur:id,name,email',
                        'validateur:id,name,email',
                        'decideur:id,name,email'
                    ])->orderBy('created_at', 'desc');
            }
        ])->find($projetId);
    }

    /**
     * Obtenir un projet avec le TDR de faisabilité actif  
     */
    public function getProjetAvecTdrFaisabilite(int $projetId): ?Projet
    {
        return $this->model->with([
            'tdrsFaisabilite' => function($query) {
                $query->whereIn('statut', ['soumis', 'en_evaluation', 'valide', 'retour_travail_supplementaire'])
                    ->with([
                        'fichiers' => function($q) { $q->active()->ordered(); },
                        'commentaires' => function($q) { $q->orderBy('created_at', 'desc'); },
                        'commentaires.commentateur:id,name,email',
                        'soumisPar:id,name,email',
                        'evaluateur:id,name,email',
                        'validateur:id,name,email',
                        'decideur:id,name,email'
                    ])->orderBy('created_at', 'desc');
            }
        ])->find($projetId);
    }

    /**
     * Obtenir les projets ayant des TDRs en attente d'évaluation
     */
    public function getProjetsAvecTdrsEnAttente(): Collection
    {
        return $this->model->whereHas('tdrs', function($query) {
                $query->whereIn('statut', ['soumis', 'en_evaluation']);
            })
            ->with([
                'tdrs' => function($query) {
                    $query->whereIn('statut', ['soumis', 'en_evaluation'])
                        ->with([
                            'soumisPar:id,name,email',
                            'evaluateur:id,name,email',
                            'commentaires' => function($q) { $q->latest()->limit(3); },
                            'commentaires.commentateur:id,name'
                        ]);
                }
            ])
            ->get();
    }

    /**
     * Obtenir les projets avec historique complet des TDRs
     */
    public function getProjetsAvecHistoriqueTdrs(array $projetIds = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($projetIds)) {
            $query->whereIn('id', $projetIds);
        }

        return $query->with([
            'tdrs' => function($tdrQuery) {
                $tdrQuery->with([
                    'fichiers' => function($q) { $q->active()->ordered(); },
                    'commentaires' => function($q) { $q->orderBy('created_at', 'desc'); },
                    'commentaires.commentateur:id,name,email',
                    'soumisPar:id,name,email',
                    'evaluateur:id,name,email',
                    'validateur:id,name,email',
                    'decideur:id,name,email'
                ])->orderBy('type')->orderBy('created_at', 'desc');
            }
        ])->get();
    }

    /**
     * Obtenir les statistiques des TDRs par projet
     */
    public function getStatistiquesTdrsProjets(): array
    {
        $projetsAvecTdrs = $this->model->has('tdrs')->with('tdrs')->get();
        
        $statistiques = [
            'total_projets_avec_tdrs' => $projetsAvecTdrs->count(),
            'par_type' => [
                'avec_prefaisabilite' => 0,
                'avec_faisabilite' => 0,
                'workflow_complet' => 0
            ],
            'par_statut' => [
                'en_attente_evaluation' => 0,
                'valides' => 0,
                'rejetes' => 0
            ],
            'moyennes' => [
                'tdrs_par_projet' => 0,
                'commentaires_par_tdr' => 0,
                'fichiers_par_tdr' => 0
            ]
        ];

        $totalTdrs = 0;
        $totalCommentaires = 0;
        $totalFichiers = 0;

        foreach ($projetsAvecTdrs as $projet) {
            $hasPrefaisabilite = $projet->tdrs->where('type', 'prefaisabilite')->isNotEmpty();
            $hasFaisabilite = $projet->tdrs->where('type', 'faisabilite')->isNotEmpty();

            if ($hasPrefaisabilite) $statistiques['par_type']['avec_prefaisabilite']++;
            if ($hasFaisabilite) $statistiques['par_type']['avec_faisabilite']++;
            if ($hasPrefaisabilite && $hasFaisabilite) $statistiques['par_type']['workflow_complet']++;

            foreach ($projet->tdrs as $tdr) {
                $totalTdrs++;
                
                // Statuts
                if (in_array($tdr->statut, ['soumis', 'en_evaluation'])) {
                    $statistiques['par_statut']['en_attente_evaluation']++;
                } elseif ($tdr->statut === 'valide') {
                    $statistiques['par_statut']['valides']++;
                } elseif ($tdr->statut === 'abandonne') {
                    $statistiques['par_statut']['rejetes']++;
                }

                // Compteurs pour moyennes
                $totalCommentaires += $tdr->commentaires->count();
                $totalFichiers += $tdr->fichiers->count();
            }
        }

        // Calcul des moyennes
        if ($projetsAvecTdrs->count() > 0) {
            $statistiques['moyennes']['tdrs_par_projet'] = round($totalTdrs / $projetsAvecTdrs->count(), 2);
        }
        
        if ($totalTdrs > 0) {
            $statistiques['moyennes']['commentaires_par_tdr'] = round($totalCommentaires / $totalTdrs, 2);
            $statistiques['moyennes']['fichiers_par_tdr'] = round($totalFichiers / $totalTdrs, 2);
        }

        return $statistiques;
    }
}