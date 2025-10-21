<?php

namespace App\Http\Resources;

use App\Http\Resources\projets\ProjetsResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NoteConceptuelleResource extends BaseApiResource
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
            'intitule' => $this->intitule,
            'parentId' => $this->parent?->hashed_id,
            'numero_contrat' => $this->numero_contrat,
            'numero_dossier' => $this->numero_dossier,
            'accept_term' => $this->accept_term,
            'statut' => $this->statut,
            'statut_libelle' => match ($this->statut) {
                1 => 'Soumise',
                default => 'Brouillon'
            },

            'valider_par' => $this->validateur ? new UserResource($this->validateur) : null,
            'rediger_par' => $this->redacteur ? new UserResource($this->redacteur) : null,
            'note_conceptuelle' => $this->note_conceptuelle,
            'projet' => $this->whenLoaded('projet', fn() => new ProjetsResource($this->projet)),
            'decision' => $this->decision,
            'historique_des_notes_conceptuelle' => $this->whenLoaded('historique_des_notes_conceptuelle', fn() => NoteConceptuelleResource::collection($this->historique_des_notes_conceptuelle)),
            'historique_des_evaluations_notes_conceptuelle' => $this->whenLoaded('historique_des_evaluations_notes_conceptuelle', function() {
                return $this->historique_des_evaluations_notes_conceptuelle->pluck("evaluations")->collapse()->map(function ($evaluation) {
                    return [
                        'id' => $evaluation->hashed_id,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->validator?->hashed_id,//valider_par,$evaluation->valider_par,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $evaluation->resultats_evaluation,
                        'statut' => $evaluation->statut
                    ];
                });
            }),
            'champs' => $this->whenLoaded('champs', function () {
                return $this->champs->map(function ($champ) {
                    return [
                        'id' => $champ->hashed_id,
                        'label' => $champ->label,
                        'attribut' => $champ->attribut,
                        'type_champ' => $champ->type_champ,
                        'valeur' => $champ->pivot->valeur,
                        'commentaire' => $champ->pivot->commentaire,
                        'updated_at' => $champ->pivot->updated_at
                    ];
                });
            }),
            'fichiers' => $this->whenLoaded('fichiers', function () {
                return FichierResource::collection($this->fichiers->sortBy('ordre'));
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
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
                'type' => 'noteconceptuelle',
                'version' => '1.0',
            ],
        ]);
    }
}
