<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface NoteConceptuelleServiceInterface extends AbstractServiceInterface
{
    public function validateNote(int $projetId, int $noteId, array $data);

    public function getValidationDetails(int $projetId, int $noteId);

    /**
     * Récupérer une note conceptuelle d'un projet
     */
    public function getForProject(int $projetId): JsonResponse;

    /**
     * Récupérer les détails de validation de l'étude de profil pour un projet
     */
    public function getDetailsEtudeProfil(int $projetId): JsonResponse;

    /**
     * Créer une évaluation pour une note conceptuelle
     */
    public function creerEvaluation(int $noteConceptuelleId, array $data): JsonResponse;

    /**
     * Mettre à jour une évaluation
     */
    public function mettreAJourEvaluation(int $evaluationId, array $data): JsonResponse;

    /**
     * Récupérer l'évaluation d'une note conceptuelle
     */
    public function getEvaluation(int $noteConceptuelleId): JsonResponse;

    /**
     * Confirmer le résultat de l'évaluation par ID de note conceptuelle
     */
    public function confirmerResultatParNote(int $noteConceptuelleId, array $data): JsonResponse;

    /**
     * Validation du projet à l'étape Etude de profil (SFD-009)
     */
    public function validerEtudeDeProfil(int $projetId, array $data): JsonResponse;

    /**
     * Soumettre ou resoumettre un rapport de faisabilité préliminaire
     * Gère la création de l'évaluation lors de la resoumission
     */
    public function soumettreRapportFaisabilitePreliminaire(int $projetId, array $data): JsonResponse;
}