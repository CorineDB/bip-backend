<?php

namespace App\Http\Resources;

use App\Http\Resources\projets\ProjetsResource;
use Carbon\Carbon;
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
            'id' => $this->hashed_id,
            'projet_id' => $this->projet->hashed_id,
            'parent_id' => $this->parent?->hashed_id,
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
            'projet' => $this->whenLoaded('projet', function () {
                return new ProjetsResource($this->projet);
            }),
            'soumis_par' => $this->whenLoaded('soumisPar', function () {
                return new UserResource($this->soumisPar);
            }),
            'validateur' => $this->whenLoaded('validateur', function () {
                return new UserResource($this->validateur);
            }),
            'rediger_par' => $this->whenLoaded('redigerPar', function () {
                return new UserResource($this->redigerPar);
            }),
            'evaluateur' => $this->whenLoaded('evaluateur', function () {
                return new UserResource($this->evaluateur);
            }),
            'historique_des_tdrs' => $this->type == 'faisabilite'
                ? $this->whenLoaded('historique_des_tdrs_faisabilite', fn() => TdrResource::collection($this->historique_des_tdrs_faisabilite))
                : $this->whenLoaded('historique_des_tdrs_prefaisabilite', fn() => TdrResource::collection($this->historique_des_tdrs_prefaisabilite)),
            "historique_des_evaluations_tdrs" => $this->type == 'faisabilite'
                ? $this->whenLoaded('historique_des_evaluations_tdrs_faisabilite', function () {
                    return $this->historique_des_evaluations_tdrs_faisabilite->pluck("evaluations")->collapse()->map(function ($evaluation) {
                        return [
                            'id' => $evaluation->hashed_id,
                            'type_evaluation' => $evaluation->type_evaluation,
                            'date_debut_evaluation' => $evaluation->date_debut_evaluation ? Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                            'date_fin_evaluation' => $evaluation->date_fin_evaluation ? Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                            'valider_le' => $evaluation->valider_le ? Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                            'valider_par' => $evaluation->validator?->hashed_id, //$evaluation->valider_par,
                            'commentaire' => $evaluation->commentaire,
                            'evaluation' => $evaluation->evaluation,
                            'resultats_evaluation' => $evaluation->resultats_evaluation,
                            'statut' => $evaluation->statut
                        ];
                    });
                })
                : $this->whenLoaded('historique_des_evaluations_tdrs_prefaisabilite', function () {
                    return $this->historique_des_evaluations_tdrs_prefaisabilite->pluck("evaluations")->collapse()->map(function ($evaluation) {
                        return [
                            'id' => $evaluation->hashed_id,
                            'type_evaluation' => $evaluation->type_evaluation,
                            'date_debut_evaluation' => $evaluation->date_debut_evaluation ? Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                            'date_fin_evaluation' => $evaluation->date_fin_evaluation ? Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                            'valider_le' => $evaluation->valider_le ? Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                            'valider_par' => $evaluation->validator?->hashed_id, //$evaluation->valider_par,
                            'commentaire' => $evaluation->commentaire,
                            'evaluation' => $evaluation->evaluation,
                            'resultats_evaluation' => $evaluation->resultats_evaluation,
                            'statut' => $evaluation->statut
                        ];
                    });
                }),

            // Fichiers par type
            'fichier_tdr' => $this->whenLoaded('fichiers', function () {
                $typeDocument = $this->type === 'faisabilite' ? 'tdr-faisabilite' : 'tdr-prefaisabilite';
                $fichier = $this->fichiers->where('metadata.type_document', $typeDocument)->first();
                return new FichierResource($fichier);
            }),

            'autres_documents' => $this->whenLoaded('fichiers', function () {
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
