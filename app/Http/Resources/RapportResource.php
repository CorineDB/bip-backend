<?php

namespace App\Http\Resources;

use App\Http\Resources\projets\ProjetsResource;
use Illuminate\Http\Request;

class RapportResource extends BaseApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hashed_id,
            'projet_id' => $this->projet?->hashed_id,
            'parent_id' => $this->parent?->hashed_id,
            'type' => $this->type,
            'statut' => $this->statut,
            'statutCode' => $this->statut === 'validé' ? 2 : ($this->statut === 'soumis' ? 1 : 0),
            'intitule' => $this->intitule,
            'checklist_suivi' =>  $this->checklist_suivi,
            'info_cabinet_etude' => $this->info_cabinet_etude,
            'recommandation' => $this->recommandation,
            'date_soumission' => $this->date_soumission,
            'date_validation' => $this->date_validation,
            'commentaire_validation' => $this->commentaire_validation,
            'decision' => $this->decision,/*
            'duree_vie' => $this->duree_vie,
            'investissement_initial' => $this->investissement_initial,
            'tri' => $this->tri,
            'van' => $this->van,
            'flux_tresorerie' => $this->flux_tresorerie,*/
            'duree_vie' => $this->when($this->type === "faisabilite", fn() => $this->duree_vie),
            'investissement_initial' => $this->when($this->type === "faisabilite", fn() => $this->investissement_initial),
            'taux_actualisation' => $this->when($this->type === "faisabilite", fn() => $this->taux_actualisation),
            'flux_tresorerie' => $this->when($this->type === "faisabilite", fn() => $this->flux_tresorerie),
            'van' => $this->when($this->type === "faisabilite", fn() => $this->van),
            'tri' => $this->when($this->type === "faisabilite", fn() => $this->tri),
            'checklist_suivi_rapport_prefaisabilite' => $this->when($this->type === "prefaisabilite", fn() => $this->checklist_suivi_rapport_prefaisabilite),
            'checklists_suivi_rapport_faisabilite' => $this->when($this->type === "faisabilite", fn() => [
                'checklist_suivi_assurance_qualite'                        => $this->checklist_suivi_assurance_qualite_rapport_etude_faisabilite,
                'checklist_etude_faisabilite_technique'                    => $this->checklist_etude_faisabilite_technique,
                'checklist_etude_faisabilite_economique'                   => $this->checklist_etude_faisabilite_economique,
                'checklist_etude_faisabilite_marche'                       => $this->checklist_etude_faisabilite_marche,
                'checklist_etude_faisabilite_organisationnelle_juridique'  => $this->checklist_etude_faisabilite_organisationnelle_et_juridique,
                'checklist_suivi_analyse_faisabilite_financiere'           => $this->checklist_suivi_analyse_faisabilite_financiere,
                'checklist_suivi_etude_analyse_impact_environnementale_et_sociale' => $this->checklist_suivi_etude_analyse_impact_environnementale_et_sociale,
            ]),
            'canevas_appreciation_rapport_final' => $this->when($this->type === "evaluation_ex_ante", fn() => $this->canevas_appreciation_rapport_final),
            'projet' => $this->whenLoaded('projet', function () {
                return new ProjetsResource($this->projet);
            }),
            'soumis_par' => $this->whenLoaded('soumisPar', function () {
                return new UserResource($this->soumisPar);
            }),
            'validateur' => $this->whenLoaded('validateur', function () {
                return new UserResource($this->validateur);
            }),

            'historique_des_rapports' => $this->whenLoaded('historique', fn() => RapportResource::collection($this->historique)),


            'historique_des_evaluations_rapports' => $this->when($this->type, function () {
                $historiqueRelation = match($this->type) {
                    'prefaisabilite' => 'historique_des_evaluations_rapports_prefaisabilite',
                    'faisabilite' => 'historique_des_evaluations_rapports_faisabilite',
                    'evaluation_ex_ante' => 'historique_des_evaluations_rapports_evaluation_ex_ante',
                    default => null
                };

                if (!$historiqueRelation || !$this->relationLoaded($historiqueRelation)) {
                    return [];
                }

                return $this->{$historiqueRelation}->pluck("evaluations")->collapse()->map(function ($evaluation) {
                    return [
                        'id' => $evaluation->hashed_id,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? \Carbon\Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? \Carbon\Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? \Carbon\Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->validator?->hashed_id,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $evaluation->resultats_evaluation,
                        'statut' => $evaluation->statut
                    ];
                });
            }),

            // Checklists de mesures d'adaptation (si projet à haut risque)
            'checklist_mesures_adaptation' => $this->when($this->type === "prefaisabilite", function () {
                return $this->projet->est_a_haut_risque ?
                    ($this->projet->mesures_adaptation ?? null) :
                    null;
            }),

            /*$this->type == "prefaisabilite" ? $this->whenLoaded('projet', function () {
                return $this->projet->est_a_haut_risque ?
                    ($this->projet->mesures_adaptation ?? null) :
                    null;
            }) : [],*/

            // Fichiers par type
            'fichiers_rapport' => $this->whenLoaded('fichiersRapport', function () {
                return FichierResource::collection($this->fichiersRapport);
            }),

            'proces_verbaux' => $this->whenLoaded('procesVerbaux', function () {
                return FichierResource::collection($this->procesVerbaux);
            }),

            'liste_presence' => $this->when($this->fichiers()->where('categorie', 'liste-presence')->first(), function () {
                return new FichierResource($this->fichiers()->where('categorie', 'liste-presence')->first());
            }),

            'documents_annexes' => $this->whenLoaded('documentsAnnexes', function () {
                return FichierResource::collection($this->documentsAnnexes);
            }),

            'tous_fichiers' => $this->whenLoaded('fichiers', function () {
                return FichierResource::collection($this->fichiers);
            }),

            // Commentaires
            'commentaires' => $this->whenLoaded('commentaires', function () {
                return CommentaireResource::collection($this->commentaires);
            }),

            // Informations métadonnées
            'est_dernier_rapport' => $this->when(
                $this->type && $this->projet_id,
                fn() => $this->estDernierRapport()
            )
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return array_merge(parent::with($request), [
            'meta' => [
                'type' => 'rapport',
                'version' => '1.0',
                'rapport_type' => $this->type,
            ],
        ]);
    }
}
