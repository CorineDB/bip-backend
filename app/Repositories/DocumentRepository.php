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
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }


    public function getCanevasRedactionNoteConceptuelle()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-redaction-note-conceptuelle');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }


    public function getCanevasAppreciationTdr()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-tdr');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get the unique canevas d'appreciation des TDRs préfaisabilité
     */
    public function getCanevasAppreciationTdrPrefaisabilite()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-tdrs-prefaisabilite');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }


    /**
     * Get the unique canevas d'appreciation des TDRs faisabilité
     */
    public function getCanevasAppreciationTdrFaisabilite()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-tdrs-faisabilite');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasRedactionTdrPrefaisabilite()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-tdr-prefaisabilite');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasRedactionTdrFaisabilite()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-tdr-faisabilite');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistSuiviRapportPrefaisabilite()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-suivi-rapport-prefaisabilite');
        })
            ->where('type', 'checklist')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistMesuresAdaptation()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'checklist-mesures-adaptation-haut-risque');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistEtudeFaisabiliteMarche()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-checklist-etude-faisabilite-marche');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistEtudeFaisabiliteEconomique()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-checklist-etude-faisabilite-economique');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistEtudeFaisabiliteTechnique()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-checklist-etude-faisabilite-technique');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistEtudeFaisabiliteFinanciere()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-checklist-analyse-faisabilite-financiere');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-checklist-etude-faisabilite-organisationnelle-juridique');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistEtudeImpactEnvironnementalEtSociale()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-checklist-etude-faisabilite-environnemental-sociale');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-checklist-suivi-assurance-qualite-etude-faisabilite');
        })
            ->where('type', 'formulaire')
            ->with([
                'sections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'sections.childSections.champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'champs' => function($query) {
                    $query->orderBy('ordre_affichage');
                },
                'categorie'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
