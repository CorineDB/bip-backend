<?php

namespace App\Repositories;

use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class DocumentRepository extends BaseRepository implements DocumentRepositoryInterface
{
    public function __construct(Document $model)
    {
        parent::__construct($model);
    }

    /**
     * Get the unique fiche idée
     */
    public function getFicheIdee()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'fiche-idee');
        })
            ->where('type', 'formulaire')
            ->orderBy('created_at', 'desc')
            ->first();
    }


    public function getCanevasRedactionNoteConceptuelle()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-redaction-note-conceptuelle');
        })
            ->where('type', 'formulaire')
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
