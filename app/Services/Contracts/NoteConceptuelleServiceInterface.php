<?php

namespace App\Services\Contracts;

interface NoteConceptuelleServiceInterface extends AbstractServiceInterface
{
    public function validateNote(int $projetId, int $noteId, array $data);
    public function getValidationDetails(int $projetId, int $noteId);
}