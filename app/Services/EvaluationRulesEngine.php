<?php

namespace App\Services;

use App\Models\Document;
use Exception;

/**
 * Moteur de règles d'évaluation configurable
 *
 * Lit les configurations depuis le canevas (evaluation_configs) et applique
 * les règles métier de manière dynamique
 */
class EvaluationRulesEngine
{
    protected Document $canevas;
    protected array $config;
    protected array $appreciations;
    protected array $results;
    protected array $rules;

    public function __construct(Document $canevas)
    {
        $this->canevas = $canevas;
        $this->loadConfiguration();
    }

    /**
     * Charger la configuration depuis le canevas
     */
    protected function loadConfiguration(): void
    {
        $this->config = $this->canevas->evaluation_configs ?? [];

        if (empty($this->config)) {
            throw new Exception("Le canevas {$this->canevas->nom} n'a pas de configuration d'évaluation (evaluation_configs)");
        }

        // Détecter automatiquement la clé d'appréciations utilisée
        $this->appreciations = $this->detectAndLoadAppreciations();
        $this->results = $this->config['results'] ?? $this->config['resultats'] ?? [];
        $this->rules = $this->config['rules'] ?? $this->config['regles'] ?? [];

        if (empty($this->appreciations)) {
            throw new Exception("Le canevas {$this->canevas->nom} n'a pas d'appréciations configurées");
        }

        if (empty($this->results)) {
            throw new Exception("Le canevas {$this->canevas->nom} n'a pas de résultats configurés");
        }

        // Valider la configuration
        $this->validateConfiguration();
    }

    /**
     * Détecter et charger les appréciations selon la structure du canevas
     * Support de plusieurs formats : appreciations, options_notation, guide_notation, guide_suivi
     */
    protected function detectAndLoadAppreciations(): array
    {
        // Format moderne : 'appreciations'
        if (isset($this->config['appreciations']) && !empty($this->config['appreciations'])) {
            return $this->normalizeAppreciations($this->config['appreciations'], 'appreciations');
        }

        // Format TDR : 'options_notation'
        if (isset($this->config['options_notation']) && !empty($this->config['options_notation'])) {
            return $this->normalizeAppreciations($this->config['options_notation'], 'options_notation');
        }

        // Format Note Conceptuelle : 'guide_notation'
        if (isset($this->config['guide_notation']) && !empty($this->config['guide_notation'])) {
            return $this->normalizeAppreciations($this->config['guide_notation'], 'guide_notation');
        }

        // Format Contrôle Qualité : 'guide_suivi'
        if (isset($this->config['guide_suivi']) && !empty($this->config['guide_suivi'])) {
            return $this->normalizeAppreciations($this->config['guide_suivi'], 'guide_suivi');
        }

        return [];
    }

    /**
     * Normaliser les appréciations vers un format commun
     *
     * @param array $appreciations Tableau d'appréciations brutes
     * @param string $format Format source (appreciations, options_notation, guide_notation, guide_suivi)
     * @return array Tableau normalisé
     */
    protected function normalizeAppreciations(array $appreciations, string $format): array
    {
        $normalized = [];

        foreach ($appreciations as $appreciation) {
            $item = [];

            // Extraire la valeur selon le format
            switch ($format) {
                case 'appreciations':
                    $item['value'] = $appreciation['value'] ?? null;
                    $item['label'] = $appreciation['label'] ?? $appreciation['libelle'] ?? null;
                    $item['description'] = $appreciation['description'] ?? null;
                    $item['score'] = $appreciation['score'] ?? 0.5;
                    $item['color'] = $appreciation['color'] ?? $appreciation['couleur'] ?? null;
                    break;

                case 'options_notation':
                case 'guide_notation':
                    $item['value'] = $appreciation['appreciation'] ?? null;
                    $item['label'] = $appreciation['libelle'] ?? null;
                    $item['description'] = $appreciation['description'] ?? null;
                    $item['score'] = $appreciation['score'] ?? $this->getDefaultScore($item['value']);
                    $item['color'] = $appreciation['couleur'] ?? $appreciation['color'] ?? null;
                    break;

                case 'guide_suivi':
                    $item['value'] = $appreciation['option'] ?? null;
                    $item['label'] = $appreciation['libelle'] ?? $appreciation['label'] ?? null;
                    $item['description'] = $appreciation['description'] ?? null;
                    $item['score'] = $appreciation['score'] ?? $this->getDefaultScore($item['value']);
                    $item['color'] = $appreciation['couleur'] ?? $appreciation['color'] ?? null;
                    break;
            }

            if ($item['value']) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }

    /**
     * Obtenir un score par défaut selon la valeur de l'appréciation
     */
    protected function getDefaultScore(?string $value): float
    {
        if (!$value) {
            return 0.5;
        }

        return match (strtolower($value)) {
            'passe', 'passable', 'valide', 'disponible' => 1.0,
            'retour', 'renvoyer', 'reserve', 'réservé' => 0.5,
            'non_accepte', 'rejete', 'rejeté' => 0.0,
            'non_applicable' => 0.75,
            default => 0.5,
        };
    }

    /**
     * Valider la configuration chargée
     */
    protected function validateConfiguration(): void
    {
        // Récupérer toutes les valeurs d'appréciations disponibles
        $appreciationValues = array_column($this->appreciations, 'value');

        // Vérifier les conditions
        $conditions = $this->rules['conditions'] ?? [];

        foreach ($conditions as $index => $ruleConfig) {
            $appreciationsConcernees = $ruleConfig['appreciations_concernees'] ?? null;

            // Vérifier que le champ appreciations_concernees existe
            if ($appreciationsConcernees === null) {
                throw new Exception("La condition #{$index} ('{$ruleConfig['name']}') n'a pas de champ 'appreciations_concernees'");
            }

            // Vérifier que chaque appréciation référencée existe
            foreach ($appreciationsConcernees as $appreciationValue) {
                if (!in_array($appreciationValue, $appreciationValues)) {
                    throw new Exception(
                        "La condition #{$index} ('{$ruleConfig['name']}') référence une appréciation inexistante : '{$appreciationValue}'. " .
                        "Appréciations disponibles : " . implode(', ', $appreciationValues)
                    );
                }
            }
        }
    }

    /**
     * Obtenir les appréciations disponibles
     */
    public function getAppreciations(): array
    {
        return $this->appreciations;
    }

    /**
     * Obtenir une appréciation par sa valeur
     */
    public function getAppreciation(string $value): ?array
    {
        foreach ($this->appreciations as $appreciation) {
            if ($appreciation['value'] === $value) {
                return $appreciation;
            }
        }
        return null;
    }

    /**
     * Obtenir les résultats possibles
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Obtenir un résultat par sa valeur
     */
    public function getResult(string $value): ?array
    {
        foreach ($this->results as $result) {
            if ($result['value'] === $value) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Compter les appréciations dans une évaluation
     *
     * @param array $evaluationsChamps Tableau des évaluations de champs
     * @return array Compteurs par type d'appréciation
     */
    public function countAppreciations(array $evaluationsChamps): array
    {
        $counts = [];

        // Initialiser les compteurs pour chaque type d'appréciation
        foreach ($this->appreciations as $appreciation) {
            $counts[$appreciation['value']] = 0;
        }

        $counts['non_evalues'] = 0;
        $counts['total'] = count($evaluationsChamps);

        // Compter les appréciations
        foreach ($evaluationsChamps as $evalChamp) {
            $appreciation = $evalChamp['appreciation'] ?? null;

            if ($appreciation && isset($counts[$appreciation])) {
                $counts[$appreciation]++;
            } else {
                $counts['non_evalues']++;
            }
        }

        return $counts;
    }

    /**
     * Calculer les statistiques complètes d'une évaluation
     *
     * @param array $evaluationsChamps Tableau des évaluations de champs
     * @param array $champsObligatoiresNonEvalues Champs obligatoires non évalués
     * @return array Statistiques complètes
     */
    public function calculateStatistics(array $evaluationsChamps, array $champsObligatoiresNonEvalues = []): array
    {
        $counts = $this->countAppreciations($evaluationsChamps);
        $counts['champs_obligatoires_non_evalues'] = count($champsObligatoiresNonEvalues);

        // Calculer les pourcentages
        $total = $counts['total'];
        $percentages = [];

        if ($total > 0) {
            foreach ($this->appreciations as $appreciation) {
                $value = $appreciation['value'];
                $percentages[$value] = round(($counts[$value] / $total) * 100, 2);
            }
            $percentages['non_evalues'] = round(($counts['non_evalues'] / $total) * 100, 2);
        }

        // Calculer la progression globale pondérée
        $progressionGlobale = $this->calculateGlobalProgression($counts);

        return [
            'counts' => $counts,
            'percentages' => $percentages,
            'progression_globale' => $progressionGlobale,
        ];
    }

    /**
     * Calculer la progression globale pondérée
     */
    protected function calculateGlobalProgression(array $counts): float
    {
        $total = $counts['total'];

        if ($total === 0) {
            return 0.0;
        }

        $scoreGlobal = 0;

        foreach ($this->appreciations as $appreciation) {
            $value = $appreciation['value'];
            $score = $appreciation['score'] ?? 0.5;
            $count = $counts[$value] ?? 0;

            $scoreGlobal += $count * $score;
        }

        return round(($scoreGlobal / $total) * 100, 2);
    }

    /**
     * Déterminer le résultat selon les règles configurées
     *
     * @param array $statistics Statistiques de l'évaluation
     * @return array Résultat avec statut, message, actions
     */
    public function determineResult(array $statistics): array
    {
        $conditions = $this->rules['conditions'] ?? [];

        if (empty($conditions)) {
            throw new Exception("Aucune condition de décision configurée pour ce canevas");
        }

        // Trier les conditions par priorité (ordre croissant)
        usort($conditions, function ($a, $b) {
            return ($a['priority'] ?? 99) <=> ($b['priority'] ?? 99);
        });

        // Évaluer chaque condition dans l'ordre de priorité
        foreach ($conditions as $ruleConfig) {
            $condition = $ruleConfig['condition'] ?? [];

            if ($this->evaluateCondition($condition, $statistics)) {
                $resultValue = $ruleConfig['result'];
                $resultConfig = $this->getResult($resultValue);

                if (!$resultConfig) {
                    throw new Exception("Résultat '{$resultValue}' non trouvé dans la configuration");
                }

                return [
                    'resultat_global' => $resultValue,
                    'message_resultat' => $ruleConfig['message'] ?? $resultConfig['message'],
                    'statut_suivant' => $resultConfig['statut_suivant'] ?? null,
                    'actions' => $resultConfig['actions'] ?? [],
                    'metadata' => $resultConfig['metadata'] ?? [],
                    'raison' => $ruleConfig['name'] ?? 'Condition satisfaite',
                    'recommandations' => $ruleConfig['recommandations'] ?? [],
                ];
            }
        }

        // Si aucune condition n'est satisfaite (ne devrait jamais arriver si condition "default" existe)
        throw new Exception("Aucune condition de décision n'a été satisfaite");
    }

    /**
     * Évaluer une condition
     *
     * @param array $condition Configuration de la condition
     * @param array $statistics Statistiques de l'évaluation
     * @return bool True si la condition est satisfaite
     */
    protected function evaluateCondition(array $condition, array $statistics): bool
    {
        $type = $condition['type'] ?? 'comparison';

        switch ($type) {
            case 'default':
                return true;

            case 'comparison':
                return $this->evaluateComparison($condition, $statistics);

            case 'and':
                return $this->evaluateAnd($condition, $statistics);

            case 'or':
                return $this->evaluateOr($condition, $statistics);

            default:
                throw new Exception("Type de condition non supporté: {$type}");
        }
    }

    /**
     * Évaluer une comparaison
     */
    protected function evaluateComparison(array $condition, array $statistics): bool
    {
        $field = $condition['field'];
        $operator = $condition['operator'];

        // Récupérer la valeur du champ (peut être imbriqué : count.passe, percentage.retour)
        $leftValue = $this->getFieldValue($field, $statistics);

        // Récupérer la valeur de comparaison (soit une valeur fixe, soit un autre champ)
        if (isset($condition['value_field'])) {
            $rightValue = $this->getFieldValue($condition['value_field'], $statistics);
        } else {
            $rightValue = $condition['value'];
        }

        // Effectuer la comparaison
        return $this->compare($leftValue, $operator, $rightValue);
    }

    /**
     * Évaluer un ET logique
     */
    protected function evaluateAnd(array $condition, array $statistics): bool
    {
        $conditions = $condition['conditions'] ?? [];

        foreach ($conditions as $subCondition) {
            if (!$this->evaluateCondition($subCondition, $statistics)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Évaluer un OU logique
     */
    protected function evaluateOr(array $condition, array $statistics): bool
    {
        $conditions = $condition['conditions'] ?? [];

        foreach ($conditions as $subCondition) {
            if ($this->evaluateCondition($subCondition, $statistics)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Récupérer la valeur d'un champ (supporte les chemins imbriqués)
     *
     * Ex: "count.passe", "percentages.retour", "progression_globale"
     */
    protected function getFieldValue(string $field, array $statistics)
    {
        $parts = explode('.', $field);
        $value = $statistics;

        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Comparer deux valeurs selon un opérateur
     */
    protected function compare($left, string $operator, $right): bool
    {
        switch ($operator) {
            case '>':
                return $left > $right;
            case '>=':
                return $left >= $right;
            case '<':
                return $left < $right;
            case '<=':
                return $left <= $right;
            case '==':
            case '=':
                return $left == $right;
            case '!=':
            case '<>':
                return $left != $right;
            default:
                throw new Exception("Opérateur non supporté: {$operator}");
        }
    }

    /**
     * Calculer les résultats complets d'une évaluation
     *
     * @param array $evaluationsChamps Tableau des évaluations
     * @param array $champsObligatoiresNonEvalues Champs obligatoires non évalués
     * @return array Résultats complets
     */
    public function evaluate(array $evaluationsChamps, array $champsObligatoiresNonEvalues = []): array
    {
        // Calculer les statistiques
        $statistics = $this->calculateStatistics($evaluationsChamps, $champsObligatoiresNonEvalues);

        // Déterminer le résultat
        $result = $this->determineResult($statistics);

        // Fusionner les statistiques avec le résultat
        return array_merge($result, [
            'nombre_total' => $statistics['counts']['total'],
            'nombre_non_evalues' => $statistics['counts']['non_evalues'],
            'champs_obligatoires_non_evalues' => $statistics['counts']['champs_obligatoires_non_evalues'],
            'counts' => $statistics['counts'],
            'percentages' => $statistics['percentages'],
            'progression_globale' => $statistics['progression_globale'],
        ]);
    }

    /**
     * Générer un résumé textuel de l'évaluation
     */
    public function generateSummary(array $result): string
    {
        $resume = "RÉSUMÉ DE L'ÉVALUATION\n\n";

        // Détails des résultats
        $resume .= "Détails des résultats :\n";
        foreach ($this->appreciations as $appreciation) {
            $value = $appreciation['value'];
            $label = $appreciation['label'];
            $count = $result['counts'][$value] ?? 0;
            $percentage = $result['percentages'][$value] ?? 0;

            $resume .= "• {$label} : {$count} ({$percentage}%)\n";
        }

        $resume .= "• Non évalués : {$result['nombre_non_evalues']} ({$result['percentages']['non_evalues']}%)\n\n";

        // Progression globale
        $resume .= "Progression globale : {$result['progression_globale']}%\n\n";

        // Résultat global
        $resume .= "Résultat global : {$result['message_resultat']}\n";

        if (!empty($result['recommandations'])) {
            $resume .= "\nRecommandations :\n";
            foreach ($result['recommandations'] as $recommandation) {
                $resume .= "- {$recommandation}\n";
            }
        }

        return $resume;
    }
}
