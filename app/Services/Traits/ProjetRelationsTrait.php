<?php

namespace App\Services\Traits;

use App\Models\LieuIntervention;
use Illuminate\Database\Eloquent\Model;

trait ProjetRelationsTrait
{
    /**
     * Valider les données de relations
     */
    protected function validateRelationsData(array $relations): array
    {
        $validated = [];

        foreach ($relations as $key => $values) {
            if (is_array($values)) {
                $validated[$key] = array_filter($values, 'is_numeric');
            }
        }

        return $validated;
    }

    /**
     * Synchroniser les relations composants programme par type
     */
    protected function syncComposantsByType(Model $projet, array $relations): void
    {
        $typeMapping = [
            'orientations_strategiques' => 'orientation-strategique-pnd',
            'objectifs_strategiques'    => 'objectif-strategique-pnd',
            'resultats_strategiques'    => 'resultats-strategique-pnd',
            'axes_pag'                  => 'axe-pag',
            'piliers_pag'               => 'pilier-pag',
            'actions_pag'               => 'action-pag'
        ];

        foreach ($typeMapping as $relationKey => $typeSlug) {
            if (isset($relations[$relationKey])) {
                $this->syncSpecificComposantType($projet, $relations[$relationKey], $typeSlug);
            }
        }
    }

    /**
     * Synchroniser un type spécifique de composant programme
     */
    private function syncSpecificComposantType(Model $projet, array $composantIds, string $typeSlug): void
    {
        // Valider que les composants appartiennent au bon type
        $validComposants = \App\Models\ComposantProgramme::whereIn('id', $composantIds)
            ->whereHas('typeProgramme', function($query) use ($typeSlug) {
                $query->where('slug', $typeSlug);
            })
            ->pluck('id')
            ->toArray();

        if (!empty($validComposants)) {
            $projet->composants()->syncWithoutDetaching($validComposants);
        }
    }

    /**
     * Nettoyer et valider les données de champs
     */
    protected function sanitizeChampData(array $champData): array
    {
        $sanitized = [];

        foreach ($champData as $champ) {
            if (!isset($champ['id'])) {
                continue;
            }

            $cleanChamp = [
                'id' => (int) $champ['id']
            ];

            if (isset($champ['valeur'])) {
                $cleanChamp['valeur'] = $this->sanitizeValue($champ['valeur']);
            }

            if (isset($champ['commentaire'])) {
                $cleanChamp['commentaire'] = strip_tags($champ['commentaire']);
            }

            // Ajouter les attributs directs
            foreach ($champ as $key => $value) {
                if (!in_array($key, ['id', 'valeur', 'commentaire'])) {
                    $cleanChamp[$key] = $this->sanitizeValue($value);
                }
            }

            $sanitized[] = $cleanChamp;
        }

        return $sanitized;
    }

    /**
     * Nettoyer une valeur
     */
    private function sanitizeValue($value)
    {
        if (is_string($value)) {
            return trim(strip_tags($value));
        }

        if (is_numeric($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map([$this, 'sanitizeValue'], $value);
        }

        return $value;
    }

    /**
     * Enregistrer l'historique des modifications
     */
    protected function logProjectModification(Model $projet, string $action, array $data = []): void
    {
        // Implémentation optionnelle pour traçabilité
        \Log::info("Projet {$action}", [
            'projet_id' => $projet->id,
            'user_id' => auth()->id() ?? null,
            'data' => $data,
            'timestamp' => now()
        ]);
    }

    /**
     * Calculer les statistiques du projet
     */
    protected function calculateProjectStats(Model $projet): array
    {
        return [
            'nb_cibles' => $projet->cibles()->count(),
            'nb_odds' => $projet->odds()->count(),
            'nb_composants' => $projet->composants()->count(),
            'nb_lieux' => $projet->lieuxIntervention()->count(),
            'nb_financements' => $projet->financements()->count(),
            'progression' => $this->calculateProgression($projet)
        ];
    }

    /**
     * Calculer la progression du projet
     */
    private function calculateProgression(Model $projet): int
    {
        $requiredFields = [
            'titre_projet', 'description', 'secteurId',
            'ministereId', 'categorieId', 'duree'
        ];

        $filledFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($projet->$field)) {
                $filledFields++;
            }
        }

        $relationsCounts = [
            'cibles' => $projet->cibles()->count(),
            'odds' => $projet->odds()->count()
        ];

        $filledRelations = array_filter($relationsCounts, function($count) {
            return $count > 0;
        });

        $totalRequirements = count($requiredFields) + count($relationsCounts);
        $totalFilled = $filledFields + count($filledRelations);

        return round(($totalFilled / $totalRequirements) * 100);
    }
}