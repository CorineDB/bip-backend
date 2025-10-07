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
            'id' => $this->id,
            'intitule' => $this->intitule,
            'numero_contrat' => $this->numero_contrat,
            'numero_dossier' => $this->numero_dossier,
            'accept_term' => $this->accept_term,
            'statut' => $this->statut,
            'statut_libelle' => match($this->statut) {
                1 => 'Soumise',
                default => 'Brouillon'
            },

            'canevas_appreciation_note_conceptuelle' => $this->canevas_appreciation_note_conceptuelle,
            'canevas_redaction_note_conceptuelle' => $this->canevas_redaction_note_conceptuelle,

            'valider_par' => $this->validateur ? new UserResource($this->validateur) : null,
            'rediger_par' => $this->redacteur ? new UserResource($this->redacteur) : null,
            'note_conceptuelle' => $this->note_conceptuelle,
            'projet' => $this->whenLoaded('projet', fn() => new ProjetsResource($this->projet)),
            'decision' => $this->decision,
            'historique_des_notes_conceptuelle' =>  $this->whenLoaded("historique_des_notes_conceptuelle", function(){
                return $this->historique_des_notes_conceptuelle;
            }),
            "historique_des_evaluations_notes_conceptuelle" => $this->whenLoaded("historique_des_evaluations_notes_conceptuelle", function(){
                $this->historique_des_evaluations_notes_conceptuelle->pluck("evaluations")->collapse()->map(function($evaluation){
                    return [
                        'id' => $evaluation->id,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->valider_par,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $evaluation->resultats_evaluation,
                        'statut' => $evaluation->statut,
                        //'champs' => collect($noteConceptuelle->note_conceptuelle)->map(function ($champ) use ($evaluation) {
                        'champs' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($evaluation) {
                            $champ_evalue = collect($evaluation->champs_evalue)
                                ->firstWhere('attribut', $champ["attribut"]);
                            return [
                                'id' => $champ["id"],
                                'label' => $champ["label"],
                                'attribut' => $champ["attribut"],
                                'valeur' => $champ["valeur"],
                                'appreciation' => $champ_evalue ? $champ_evalue["pivot"]["note"] : null,
                                'commentaire' => $champ_evalue ? $champ_evalue["pivot"]["commentaires"] : null,
                                'date_note' => $champ_evalue ? $champ_evalue["pivot"]["date_note"] : null,
                                'updated_at' => $champ_evalue ? $champ_evalue["pivot"]["updated_at"] : null,
                            ];
                        }),
                    ];
                });
            }),
            'champs' => $this->whenLoaded('champs', function() {
                return $this->champs->map(function ($champ) {
                    return [
                        'id' => $champ->id,
                        'label' => $champ->label,
                        'attribut' => $champ->attribut,
                        'type_champ' => $champ->type_champ,
                        'valeur' => $champ->pivot->valeur,
                        'commentaire' => $champ->pivot->commentaire,
                        'updated_at' => $champ->pivot->updated_at
                    ];
                });
            }),
            'fichiers' => $this->whenLoaded('fichiers', function() {
                return FichierResource::collection($this->fichiers->sortBy('ordre'));
                return $this->fichiers->sortBy('ordre')->map(function ($fichier) {
                    return [
                        'id' => $fichier->id,
                        'nom_original' => $fichier->nom_original,
                        'categorie' => $fichier->categorie,
                        'description' => $fichier->description,
                        'extension' => $fichier->extension,
                        'mime_type' => $fichier->mime_type,
                        'taille' => $fichier->taille,
                        'taille_formatee' => $fichier->taille_formatee,
                        'url' => $fichier->url,
                        'is_image' => $fichier->is_image,
                        'is_document' => $fichier->is_document,
                        'ordre' => $fichier->ordre,
                        'metadata' => $fichier->metadata,
                        'type_document' => $fichier->metadata['type_document'] ?? null,
                        'uploaded_by' => $fichier->uploadedBy ? new UserResource($fichier->uploadedBy) : null,
                        'created_at' => $fichier->created_at?->toISOString(),
                    ];
                })->values();
            }),
            'parent' => $this->whenLoaded("parent", function(){
                return new NoteConceptuelleResource($this->parent->load("parent"));
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
