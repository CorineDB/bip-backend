<?php

namespace App\Repositories;

use App\Models\CategorieCritere;
use App\Models\Secteur;
use App\Repositories\Contracts\CategorieCritereRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use Exception;

class CategorieCritereRepository extends BaseRepository implements CategorieCritereRepositoryInterface
{
    public function __construct(CategorieCritere $model)
    {
        parent::__construct($model);
    }

    /**
     * Find CategorieCritere by type
     */
    public function findByType(string $type)
    {
        return $this->model->where('type', $type)->first();
    }

    public function getCanevasEvaluationClimatique(): CategorieCritere|null
    {
        $grille = $this->findByAttribute('slug', 'evaluation-preliminaire-multi-projet-impact-climatique');

        return $grille ? ($grille->load(['criteres.notations', 'fichiers'])) : null;
    }

    public function getCanevasAMC(): CategorieCritere|null
    {
        $grille = $this->findByAttribute('slug', 'grille-analyse-multi-critere');

        return $grille ? ($grille->load(['criteres.notations', 'notations', 'fichiers'])) : null;
    }

    public function getCanevasEvaluationDePertinence(): CategorieCritere|null
    {
        $grille = $this->findByAttribute('slug', 'grille-evaluation-pertinence-idee-projet');

        return $grille ? ($grille->load(['criteres.notations', 'fichiers'])) : null;
    }



    public function getChecklistMesuresAdaptationSecteur($idSecteur): CategorieCritere|null{

        try {
            // Vérifier que le secteur existe et n'est pas un grand secteur
            $secteur = Secteur::whereIn('type', ['secteur', 'sous-secteur'])->findOrFail($idSecteur);

            // Déterminer l'ID du secteur à utiliser pour le filtrage
            $secteurIdPourFiltrage = $idSecteur;

            // Si c'est un sous-secteur, récupérer son secteur parent pour le filtrage
            if ($secteur->type->value === 'sous-secteur') {
                $secteurParent = $secteur->parent;
                if ($secteurParent) {
                    $secteurIdPourFiltrage = $secteurParent->id;
                }
            }

            $checklistCategorie = $this->findByAttribute('slug', 'checklist-mesures-adaptation-haut-risque');

            // Charger la checklist avec les critères et notations filtrés par secteur
            return $checklistCategorie ? ($checklistCategorie->load([
                'criteres' => function($query) use ($secteurIdPourFiltrage) {
                    $query->withNotationsDuSecteur($secteurIdPourFiltrage);
                }
            ])) : null;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
