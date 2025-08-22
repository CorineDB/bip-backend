<?php

namespace App\Repositories;

use App\Models\NoteConceptuelle;
use App\Repositories\Contracts\NoteConceptuelleRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class NoteConceptuelleRepository extends BaseRepository implements NoteConceptuelleRepositoryInterface
{
    public function __construct(NoteConceptuelle $model)
    {
        parent::__construct($model);
    }

    /**
     * Trouver une note conceptuelle avec ses fichiers
     */
    public function findWithFiles(int|string $id): ?NoteConceptuelle
    {
        return $this->model->with(['fichiers.uploadedBy'])->find($id);
    }

    /**
     * Override findById pour inclure les fichiers par défaut
     */
    public function findById(
        $modelId,
        array $attribut = ['*'],
        array $relations = [],
        array $appends = []
    ): ?NoteConceptuelle {
        // Ajouter la relation fichiers par défaut si pas déjà présente
        if (!in_array('fichiers', $relations) && !in_array('fichiers.uploadedBy', $relations)) {
            $relations[] = 'fichiers.uploadedBy';
        }

        return parent::findById($modelId, $attribut, $relations, $appends);
    }
}