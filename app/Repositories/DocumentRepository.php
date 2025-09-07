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

    /**
     * Get the unique canevas d'appreciation des TDRs faisabilité
     */
    public function getCanevasAppreciationNoteConceptuelle()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-note-conceptuelle');
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

    /**
     * Get the unique canevas d'appreciation des TDRs faisabilité
     */
    public function getCanevasAppreciationTdrFaisabilite()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-tdrs-faisabilite');
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

    /*
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
    */

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

    public function getCanevasChecklisteEtudeFaisabiliteMarche()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-etude-faisabilite-marche');
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

    public function getCanevasChecklisteEtudeFaisabiliteEconomique()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-etude-faisabilite-economique');
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

    public function getCanevasChecklisteEtudeFaisabiliteTechnique()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-etude-faisabilite-technique');
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

    public function getCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-de-suivi-analyse-de-faisabilite-financiere');
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

    public function getCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-de-suivi-etude-de-faisabilite-organisationnelle-juridique');
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

    public function getCanevasChecklisteSuiviEtudeImpactEnvironnementaleEtSociale()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-de-suivi-etude-analyse-impact-environnementale-sociale');
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

    public function getCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite()
    {
        return $this->model->whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-suivi-assurance-qualite-rapport-etude-faisabilite');
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
}
