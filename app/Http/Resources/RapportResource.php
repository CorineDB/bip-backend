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
            'id' => $this->id,
            'projet_id' => $this->projet_id,
            //'parent_id' => $this->parent_id,
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
            'decision' => $this->decision,
            //'champs' => $this->champs,
            // Relations
            /*
            'projet' => $this->whenLoaded('projet', function() {
                $projetResource = new ProjetsResource($this->projet);
                // Ajouter les checklists de mesures d'adaptation si projet à haut risque
                if ($this->projet->est_a_haut_risque) {
                    $projetResource->additional([
                        'checklist_mesures_adaptation' => $this->projet->metadata['checklist_controle_adaptation_haut_risque'] ?? null
                    ]);
                }
                return $projetResource;
            }),
            */
            'projet' => $this->whenLoaded('projet', function(){
                return new ProjetsResource($this->projet);
            }),
            'soumis_par' => $this->whenLoaded('soumisPar', function(){
                return new UserResource($this->soumisPar);
            }),
            'validateur' => $this->whenLoaded('validateur', function(){
                return new UserResource($this->validateur);
            }),

            'historique_des_rapports' =>  $this->when(($this->type == 'faisabilite' || $this->type == 'prefaisabilite'), function(){
                return $this->type == 'faisabilite' ? $this->historique_des_rapports_faisabilite : $this->historique_des_rapports_prefaisabilite;
            }),
            'historique_des_evaluations_rapports' =>  $this->when(($this->type == 'faisabilite' || $this->type == 'prefaisabilite'), function(){
                return $this->type == 'faisabilite' ? $this->historique_des_evaluations_rapports_faisabilite : $this->historique_des_evaluations_rapports_prefaisabilite;
            }),

            /*
            'parent' => $this->whenLoaded('parent', new self($this->parent)),
            'enfants' => $this->whenLoaded('enfants', self::collection($this->enfants)), */

            // Checklists de mesures d'adaptation (si projet à haut risque)
            'checklist_mesures_adaptation' => $this->type == "prefaisabilite" ? $this->whenLoaded('projet', function() {
                return $this->projet->est_a_haut_risque ?
                    ($this->projet->mesures_adaptation ?? null) :
                    null;
            }) : [],

            // Fichiers par type
            'fichiers_rapport' => $this->whenLoaded('fichiersRapport', function() {
                return FichierResource::collection($this->fichiersRapport);
            }),

            'proces_verbaux' => $this->whenLoaded('procesVerbaux', function() {
                return FichierResource::collection($this->procesVerbaux);
            }),

            'liste_presence' => $this->when($this->fichiers()->where('categorie', 'liste-presence')->first(), function() {
                return new FichierResource($this->fichiers()->where('categorie', 'liste-presence')->first());
            }),

            'documents_annexes' => $this->whenLoaded('documentsAnnexes', function() {
                return FichierResource::collection($this->documentsAnnexes);
            }),

            'tous_fichiers' => $this->whenLoaded('fichiers', function() {
                return FichierResource::collection($this->fichiers);
            }),

            // Commentaires
            'commentaires' => $this->whenLoaded('commentaires', function() {
                return CommentaireResource::collection($this->commentaires);
            }),

            // Informations métadonnées
            'est_dernier_rapport' => $this->when(
                $this->type && $this->projet_id,
                fn() => $this->estDernierRapport()
            ),
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
