<?php

namespace App\Traits;

use App\Services\EvaluationRulesEngine;

/**
 * Trait pour gérer les règles d'évaluation configurables
 *
 * Utilisé par le modèle Document pour faciliter l'accès aux configurations
 * d'évaluation et au moteur de règles
 */
trait HasEvaluationRules
{
    /**
     * Obtenir le moteur de règles pour ce canevas
     */
    public function getEvaluationEngine(): EvaluationRulesEngine
    {
        return new EvaluationRulesEngine($this);
    }

    /**
     * Vérifier si le canevas a une configuration d'évaluation
     */
    public function hasEvaluationConfig(): bool
    {
        return !empty($this->evaluation_configs);
    }

    /**
     * Obtenir les appréciations disponibles
     */
    public function getEvaluationAppreciations(): array
    {
        return $this->evaluation_configs['appreciations'] ?? [];
    }

    /**
     * Obtenir les résultats possibles
     */
    public function getEvaluationResults(): array
    {
        return $this->evaluation_configs['results'] ?? [];
    }

    /**
     * Obtenir les règles de décision
     */
    public function getEvaluationRules(): array
    {
        return $this->evaluation_configs['rules'] ?? [];
    }

    /**
     * Mettre à jour la configuration d'évaluation
     */
    public function setEvaluationConfig(array $config): void
    {
        $this->evaluation_configs = $config;
        $this->save();
    }

    /**
     * Ajouter une appréciation à la configuration
     */
    public function addAppreciation(array $appreciation): void
    {
        $config = $this->evaluation_configs ?? [];
        $config['appreciations'] = $config['appreciations'] ?? [];
        $config['appreciations'][] = $appreciation;

        $this->evaluation_configs = $config;
        $this->save();
    }

    /**
     * Ajouter un résultat à la configuration
     */
    public function addResult(array $result): void
    {
        $config = $this->evaluation_configs ?? [];
        $config['results'] = $config['results'] ?? [];
        $config['results'][] = $result;

        $this->evaluation_configs = $config;
        $this->save();
    }

    /**
     * Ajouter une règle de décision à la configuration
     */
    public function addRule(array $rule): void
    {
        $config = $this->evaluation_configs ?? [];
        $config['rules']['conditions'] = $config['rules']['conditions'] ?? [];
        $config['rules']['conditions'][] = $rule;

        $this->evaluation_configs = $config;
        $this->save();
    }

    /**
     * Évaluer des appréciations selon les règles configurées
     *
     * @param array $evaluationsChamps Tableau des évaluations de champs
     * @param array $champsObligatoiresNonEvalues Champs obligatoires non évalués
     * @return array Résultats de l'évaluation
     */
    public function evaluateAppreciations(array $evaluationsChamps, array $champsObligatoiresNonEvalues = []): array
    {
        $engine = $this->getEvaluationEngine();
        return $engine->evaluate($evaluationsChamps, $champsObligatoiresNonEvalues);
    }

    /**
     * Obtenir les seuils de progression
     */
    public function getProgressionThresholds(): array
    {
        return $this->evaluation_configs['rules']['progression']['thresholds'] ?? [
            'excellent' => 90,
            'tres_bien' => 75,
            'bien' => 60,
            'passable' => 40,
            'insuffisant' => 0
        ];
    }

    /**
     * Déterminer le statut de progression
     */
    public function determineProgressionStatus(float $progressionGlobale): string
    {
        $thresholds = $this->getProgressionThresholds();

        if ($progressionGlobale >= $thresholds['excellent']) {
            return 'excellent';
        } elseif ($progressionGlobale >= $thresholds['tres_bien']) {
            return 'tres_bien';
        } elseif ($progressionGlobale >= $thresholds['bien']) {
            return 'bien';
        } elseif ($progressionGlobale >= $thresholds['passable']) {
            return 'passable';
        } else {
            return 'insuffisant';
        }
    }

    /**
     * Valider la structure de la configuration d'évaluation
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateEvaluationConfig(): array
    {
        $errors = [];

        if (!$this->hasEvaluationConfig()) {
            $errors[] = "Aucune configuration d'évaluation définie";
            return ['valid' => false, 'errors' => $errors];
        }

        $config = $this->evaluation_configs;

        // Vérifier les appréciations
        if (empty($config['appreciations'])) {
            $errors[] = "Aucune appréciation définie";
        } else {
            foreach ($config['appreciations'] as $index => $appreciation) {
                if (empty($appreciation['value'])) {
                    $errors[] = "Appréciation #{$index}: 'value' manquant";
                }
                if (empty($appreciation['label'])) {
                    $errors[] = "Appréciation #{$index}: 'label' manquant";
                }
                if (!isset($appreciation['score'])) {
                    $errors[] = "Appréciation #{$index}: 'score' manquant";
                }
            }
        }

        // Vérifier les résultats
        if (empty($config['results'])) {
            $errors[] = "Aucun résultat défini";
        } else {
            foreach ($config['results'] as $index => $result) {
                if (empty($result['value'])) {
                    $errors[] = "Résultat #{$index}: 'value' manquant";
                }
                if (empty($result['statut_suivant'])) {
                    $errors[] = "Résultat #{$index}: 'statut_suivant' manquant";
                }
            }
        }

        // Vérifier les règles
        if (empty($config['rules']['conditions'])) {
            $errors[] = "Aucune condition de décision définie";
        } else {
            $hasDefault = false;
            foreach ($config['rules']['conditions'] as $index => $rule) {
                if (empty($rule['condition'])) {
                    $errors[] = "Règle #{$index}: 'condition' manquant";
                }
                if (empty($rule['result'])) {
                    $errors[] = "Règle #{$index}: 'result' manquant";
                }
                if (($rule['condition']['type'] ?? '') === 'default') {
                    $hasDefault = true;
                }
            }

            if (!$hasDefault) {
                $errors[] = "Aucune condition par défaut (type: 'default') définie";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
