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
}