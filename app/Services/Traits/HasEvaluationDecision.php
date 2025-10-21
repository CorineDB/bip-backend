<?php

namespace App\Services\Traits;

/**
 * Trait pour gérer l'évaluation et la prise de décision basée sur des règles configurables
 * Utilisable par tout service qui nécessite une évaluation (Notes Conceptuelles, TDR, etc.)
 */
trait HasEvaluationDecision
{
    /**
     * Déterminer le résultat de l'évaluation selon les règles configurées du canevas
     *
     * @param array $evaluationConfig Configuration du canevas (evaluation_configs)
     * @param array $compteurs Compteurs de l'évaluation (passe, retour, non_accepte, etc.)
     * @return array Résultat avec statut, message, notification, raisons, recommandations
     */
    protected function determinerResultatAvecConfig(array $evaluationConfig, array $compteurs): array
    {
        // Récupérer l'algorithme de décision
        $algorithme = $evaluationConfig['algorithme_decision']['etapes'] ?? [];

        if (empty($algorithme)) {
            // Si pas d'algorithme configuré, utiliser la logique par défaut
            return $this->appliquerLogiqueParDefaut($evaluationConfig, $compteurs);
        }

        // Trier les étapes par ordre
        usort($algorithme, fn($a, $b) => ($a['ordre'] ?? 0) <=> ($b['ordre'] ?? 0));

        // Exécuter chaque étape de l'algorithme
        foreach ($algorithme as $etape) {
            $resultat = $this->executerEtapeAlgorithme($etape, $compteurs);

            // Si l'étape retourne un résultat final, on arrête
            if ($resultat !== null) {
                return $this->construireReponseEvaluation($resultat, $evaluationConfig, $compteurs);
            }
        }

        // Par défaut, si aucune étape n'a donné de résultat
        return $this->construireReponseEvaluation('retour', $evaluationConfig, $compteurs);
    }

    /**
     * Exécuter une étape de l'algorithme
     */
    private function executerEtapeAlgorithme(array $etape, array $compteurs): ?string
    {
        $condition = $etape['condition'] ?? '';

        return match ($condition) {
            'check_completude' => $this->verifierCompletude($etape, $compteurs),
            'check_non_accepte' => $this->verifierNonAccepte($etape, $compteurs),
            'count_retour' => $this->compterRetours($etape, $compteurs),
            'check_final' => $this->determinerResultatFinal($compteurs),
            default => null // Condition non reconnue
        };
    }

    /**
     * Vérifier que toutes les questions ont une évaluation
     */
    private function verifierCompletude(array $etape, array $compteurs): ?string
    {
        $nonEvalues = $compteurs['non_evalues'] ?? 0;
        $obligatoiresNonEvalues = $compteurs['obligatoires_non_evalues'] ?? 0;

        if ($obligatoiresNonEvalues > 0 || $nonEvalues > 0) {
            return $etape['action_si_echec'] ?? null;
        }

        return null; // Continue vers l'étape suivante
    }

    /**
     * Vérifier qu'aucune question n'a été évaluée comme 'non_accepte'
     */
    private function verifierNonAccepte(array $etape, array $compteurs): ?string
    {
        $nonAccepte = $compteurs['non_accepte'] ?? 0;

        if ($nonAccepte > 0) {
            return $etape['action_si_echec'] ?? null;
        }

        return null;
    }

    /**
     * Compter le nombre de 'retour' et vérifier le seuil
     */
    private function compterRetours(array $etape, array $compteurs): ?string
    {
        $retour = $compteurs['retour'] ?? 0;
        $seuilMax = $etape['seuil_max'] ?? 9;

        if ($retour > $seuilMax) {
            return $etape['action_si_depassement'] ?? 'non_accepte';
        }

        // Respecte le seuil, passer à l'action suivante
        $actionSiRespecte = $etape['action_si_respecte'] ?? null;

        // Si l'action est "check_final", exécuter la logique finale
        if ($actionSiRespecte === 'check_final') {
            return $this->determinerResultatFinal($compteurs);
        }

        return null;
    }

    /**
     * Déterminer le résultat final basé sur les compteurs
     * Cette méthode est autonome et vérifie tous les cas possibles
     */
    private function determinerResultatFinal(array $compteurs): string
    {
        $passe = $compteurs['passe'] ?? 0;
        $retour = $compteurs['retour'] ?? 0;
        $nonAccepte = $compteurs['non_accepte'] ?? 0;
        $total = $compteurs['total'] ?? 0;
        $nonEvalues = $compteurs['non_evalues'] ?? 0;

        // Priorité 1: Si on a des réponses non acceptées
        if ($nonAccepte > 0) {
            return 'non_accepte';
        }

        // Priorité 2: Si toutes les questions ont reçu "Passe"
        if ($passe === $total && $nonEvalues === 0) {
            return 'passe';
        }

        // Priorité 3: Si on a des retours mais dans les limites acceptables
        if ($retour > 0) {
            return 'retour';
        }

        // Par défaut, passe si pas de problèmes majeurs
        return 'passe';
    }

    /**
     * Construire la réponse finale avec les informations de la règle
     */
    private function construireReponseEvaluation(string $resultat, array $evaluationConfig, array $compteurs): array
    {
        $reglesDecision = $evaluationConfig['regles_decision'] ?? [];
        $regle = $reglesDecision[$resultat] ?? [];

        // Calculer les raisons et recommandations
        $raisons = $this->calculerRaisonsEvaluation($resultat, $compteurs, $evaluationConfig);
        $recommandations = $this->calculerRecommandationsEvaluation($resultat, $compteurs);

        return [
            'statut' => $resultat,
            'message' => $regle['message'] ?? "Résultat: {$resultat}",
            'description' => $regle['description'] ?? '',
            'statut_final' => $regle['statut_final'] ?? $resultat,
            'notification' => $regle['notification'] ?? [
                'titre' => "Évaluation {$resultat}",
                'message' => $regle['message'] ?? '',
                'type' => $this->getTypeNotification($resultat)
            ],
            'raisons' => $raisons,
            'recommandations' => $recommandations
        ];
    }

    /**
     * Calculer les raisons du résultat
     */
    private function calculerRaisonsEvaluation(string $resultat, array $compteurs, array $evaluationConfig): array
    {
        $raisons = [];
        $nonAccepte = $compteurs['non_accepte'] ?? 0;
        $retour = $compteurs['retour'] ?? 0;
        $obligatoiresNonEvalues = $compteurs['obligatoires_non_evalues'] ?? 0;

        if ($obligatoiresNonEvalues > 0) {
            $raisons[] = "Des questions obligatoires n'ont pas été complétées ({$obligatoiresNonEvalues} champ(s))";
        }

        if ($nonAccepte > 0) {
            $raisons[] = "{$nonAccepte} réponse(s) évaluée(s) comme \"Non accepté\"";
        }

        if ($retour > 0 && $resultat === 'non_accepte') {
            $regles = $evaluationConfig['regles_decision']['retour'] ?? [];
            $maxRetour = $regles['max_retour_allowed'] ?? 9;
            $raisons[] = "{$retour} réponses évaluées comme \"Retour\" (seuil maximum: {$maxRetour})";
        }

        return $raisons;
    }

    /**
     * Calculer les recommandations
     */
    private function calculerRecommandationsEvaluation(string $resultat, array $compteurs): array
    {
        $recommandations = [];
        $retour = $compteurs['retour'] ?? 0;
        $nonEvalues = $compteurs['non_evalues'] ?? 0;
        $obligatoiresNonEvalues = $compteurs['obligatoires_non_evalues'] ?? 0;

        if ($obligatoiresNonEvalues > 0) {
            $recommandations[] = "Compléter tous les champs obligatoires avant soumission";
        }

        if ($resultat === 'non_accepte') {
            $recommandations[] = "Revoir complètement les sections marquées comme \"Non accepté\"";
            if ($retour > 0) {
                $recommandations[] = "Réviser en profondeur le document";
            }
        }

        if ($resultat === 'retour' && $retour > 0) {
            $recommandations[] = "Améliorer les {$retour} point(s) marqué(s) comme \"Retour\"";
        }

        if ($nonEvalues > 0) {
            $recommandations[] = "Attendre l'évaluation des {$nonEvalues} champ(s) restant(s)";
        }

        return $recommandations;
    }

    /**
     * Obtenir le type de notification selon le résultat
     */
    private function getTypeNotification(string $resultat): string
    {
        return match ($resultat) {
            'passe' => 'success',
            'retour' => 'warning',
            'non_accepte' => 'error',
            default => 'info'
        };
    }

    /**
     * Logique par défaut si aucun algorithme n'est configuré dans le canevas
     */
    private function appliquerLogiqueParDefaut(array $evaluationConfig, array $compteurs): array
    {
        $obligatoiresNonEvalues = $compteurs['obligatoires_non_evalues'] ?? 0;
        $nonAccepte = $compteurs['non_accepte'] ?? 0;
        $retour = $compteurs['retour'] ?? 0;
        $passe = $compteurs['passe'] ?? 0;
        $total = $compteurs['total'] ?? 0;
        $nonEvalues = $compteurs['non_evalues'] ?? 0;

        // Règle 1: Champs obligatoires non complétés
        if ($obligatoiresNonEvalues > 0) {
            return $this->construireReponseEvaluation('non_accepte', $evaluationConfig, $compteurs);
        }

        // Règle 2: Questions non acceptées
        if ($nonAccepte > 0) {
            return $this->construireReponseEvaluation('non_accepte', $evaluationConfig, $compteurs);
        }

        // Règle 3: Trop de retours (seuil par défaut: 9)
        if ($retour >= 10) {
            return $this->construireReponseEvaluation('non_accepte', $evaluationConfig, $compteurs);
        }

        // Règle 4: Toutes les questions passées
        if ($passe === $total && $nonEvalues === 0) {
            return $this->construireReponseEvaluation('passe', $evaluationConfig, $compteurs);
        }

        // Sinon: Retour pour travail supplémentaire
        return $this->construireReponseEvaluation('retour', $evaluationConfig, $compteurs);
    }
}
