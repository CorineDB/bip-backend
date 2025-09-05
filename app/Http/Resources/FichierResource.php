<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FichierResource extends BaseApiResource
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
            'nom_original' => $this->nom_original,
            'nom_stockage' => $this->nom_stockage,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'taille' => $this->taille,
            'taille_formatee' => $this->taille_formatee,
            'description' => $this->description,
            'categorie' => $this->categorie,
            'is_public' => $this->is_public,
            'is_active' => $this->is_active,
            'commentaire' => $this->commentaire,

            // Informations calculées
            'is_image' => $this->is_image,
            'is_document' => $this->is_document,

            // URLs sécurisées
            'urls' => [
                'view' => route('api.fichiers.show', $this->id),
                'download' => Storage::url($this->chemin),
            ],

            // Statistiques
            'statistiques' => [
                'nb_telechargements' => $this->nb_telechargements ?? 0,
                'nb_vues' => $this->nb_vues ?? 0,
            ],

            // Relations
            'uploaded_by' => [
                'id' => $this->uploaded_by,
                'nom' => $this->whenLoaded('uploadedBy', fn() => $this->uploadedBy->nom),
                'email' => $this->whenLoaded('uploadedBy', fn() => $this->uploadedBy->email),
            ],

            // Ressource attachée si applicable
            'ressource_attachee' => $this->when(
                $this->fichier_attachable_id && $this->fichier_attachable_type,
                [
                    'type' => $this->fichier_attachable_type,
                    'id' => $this->fichier_attachable_id,
                    'nom' => $this->whenLoaded('fichierAttachable', fn() => $this->fichierAttachable->nom ?? $this->fichierAttachable->titre ?? 'Sans nom')
                ]
            ),

            // Permissions de l'utilisateur actuel
            /* 'permissions' => $this->when(
                auth()->check(),
                [
                    'can_view' => $this->canView(),
                    'can_download' => $this->canDownload(),
                    'can_delete' => $this->canDelete(),
                    'can_share' => $this->canShare(),
                ]
            ), */

            // Métadonnées
            'metadata' => $this->metadata,
            'hash_md5' => $this->hash_md5,

            // Horodatage
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
                'type' => 'fichier',
                'version' => '2.0',
                'permissions_info' => [
                    'public' => 'Fichier accessible à tous',
                    'private' => 'Fichier accessible uniquement au propriétaire et aux personnes autorisées',
                    'attached' => 'Fichier rattaché à une ressource spécifique'
                ],
                'taille_limits' => [
                    'max_upload' => '20MB',
                    'supported_formats' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png', 'gif']
                ]
            ],
        ]);
    }

    /**
     * Vérifier si l'utilisateur peut voir le fichier
     */
    protected function canView(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Admin peut tout voir
        if ($user->hasRole('admin')) return true;

        // Propriétaire peut voir
        if ($this->uploaded_by === $user->id) return true;

        // Fichier public
        if ($this->is_public) return true;

        // TODO: Vérifier permissions sur ressource attachée

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut télécharger le fichier
     */
    protected function canDownload(): bool
    {
        return $this->canView(); // Même logique pour l'instant
    }

    /**
     * Vérifier si l'utilisateur peut supprimer le fichier
     */
    protected function canDelete(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Admin peut tout supprimer
        if ($user->hasRole('admin')) return true;

        // Propriétaire peut supprimer ses fichiers non attachés
        if ($this->uploaded_by === $user->id && !$this->fichier_attachable_id) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut partager le fichier
     */
    protected function canShare(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Admin peut partager
        if ($user->hasRole('admin')) return true;

        // Propriétaire peut partager
        if ($this->uploaded_by === $user->id) return true;

        return false;
    }
}