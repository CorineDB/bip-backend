<?php

namespace App\Http\Resources;

use App\Http\Resources\projets\ProjetsResource;
use Illuminate\Http\Request;

class TdrResource extends BaseApiResource
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
            'type' => $this->type,
            'statut' => $this->statut,
            'statutCode' => $this->statut != 'brouillon' ? 1 : 0,
            'resume' => $this->resume,
            'date_soumission' => $this->date_soumission,
            'date_evaluation' => $this->date_evaluation,
            'date_validation' => $this->date_validation,

            'numero_contrat' => $this->numero_contrat,
            'numero_dossier' => $this->numero_dossier,
            'accept_term' => $this->accept_term,

            'evaluations_detaillees' => $this->evaluations_detaillees,
            'termes_de_reference' => $this->termes_de_reference,
            'commentaire_evaluation' => $this->commentaire_evaluation,
            'commentaire_validation' => $this->commentaire_validation,
            'decision_validation' => $this->decision_validation,
            'commentaire_decision' => $this->commentaire_decision,
            'resultats_evaluation' => $this->resultats_evaluation,
            'nombre_passe' => $this->nombre_passe,
            'nombre_retour' => $this->nombre_retour,
            'nombre_non_accepte' => $this->nombre_non_accepte,

            // Relations
            'projet' => $this->whenLoaded('projet', function(){
                return new ProjetsResource($this->projet);
            }),
            'soumis_par' => $this->whenLoaded('soumisPar', function(){
                return new UserResource($this->soumisPar);
            }),
            'validateur' => $this->whenLoaded('validateur', function(){
                return new UserResource($this->validateur);
            }),
            'rediger_par' => $this->whenLoaded('redigerPar', function(){
                return new UserResource($this->redigerPar);
            }),
            'evaluateur' => $this->whenLoaded('evaluateur', function(){
                return new UserResource($this->evaluateur);
            }),

            // Fichiers par type
            'fichier_tdr' => $this->whenLoaded('fichiers', function() {
                $typeDocument = $this->type === 'faisabilite' ? 'tdr-faisabilite' : 'tdr-prefaisabilite';
                $fichier = $this->fichiers->where('metadata.type_document', $typeDocument)->first();
                return new FichierResource($fichier);
            }),

            'autres_documents' => $this->whenLoaded('fichiers', function() {
                $typeDocument = $this->type === 'faisabilite' ? 'autre-document-faisabilite' : 'autre-document-prefaisabilite';
                return FichierResource::collection($this->fichiers->where('metadata.type_document', $typeDocument)->values());
            }),
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
                'type' => 'tdr',
                'version' => '1.0',
            ],
        ]);
    }
}
