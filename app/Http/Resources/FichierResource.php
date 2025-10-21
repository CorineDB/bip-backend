<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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
            'id' => $this?->hashed_id,
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

            // URLs sécurisées via hash MD5
            'urls' => [
                'view' => route('api.fichiers.view', ['hash' => $this->hash_md5]),
                'download' => URL::signedRoute('api.fichiers.download', ['hash' => $this->hash_md5], now()->addMinutes(5)),
                'details' => route('api.fichiers.show', $this->id), // Garder pour les détails par ID
            ],

            // Statistiques
            'statistiques' => [
                'nb_telechargements' => $this->nb_telechargements ?? 0,
                'nb_vues' => $this->nb_vues ?? 0,
            ],

            // Relations
            'uploaded_by' => [
                'id' => $this->uploadedBy?->hashed_id,//uploaded_by,
                'nom' => $this->whenLoaded('uploadedBy', fn() => $this->uploadedBy->personne->nom),
                'email' => $this->whenLoaded('uploadedBy', fn() => $this->uploadedBy->email),
            ],

            // Ressource attachée si applicable
            'ressource_attachee' => $this->when(
                $this->fichier_attachable_id && $this->fichier_attachable_type,
                [
                    'type' => $this->fichier_attachable_type,
                    'id' => $this->fichierAttachable?->hashed_id,//fichier_attachable_id,
                    'nom' => $this->whenLoaded('fichierAttachable', fn() => $this->fichierAttachable->nom ?? $this->fichierAttachable->titre ?? 'Sans nom')
                ]
            ),

            // Permissions de l'utilisateur actuel
            'permissions' => $this->when(
                auth()->check(),
                function() {
                    $user = auth()->user();
                    return [
                        'can_view' => $this->canView(),
                        'can_download' => $this->canDownload(),
                        'can_delete' => $this->canDelete(),
                        'can_share' => $this->canShare(),
                        'can_edit' => $this->hasPermission($user, 'edit') || $this->uploaded_by === $user->id,
                    ];
                }
            ),

            // Partages actifs
            'partages' => $this->when(
                auth()->check() && ($this->uploaded_by === auth()->id() || auth()->user()->hasRole('admin')),
                function() {
                    return $this->permissions()
                        ->with(['user:id,email,personneId', 'user.personne:id,nom,prenom'])
                        ->where('is_active', true)
                        ->where(function($q) {
                            $q->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                        })
                        ->get()
                        ->map(function($permission) {
                            return [
                                'user' => [
                                    'id' => $permission->user?->hashed_id,
                                    'nom' => $permission->user->personne->nom ?? '',
                                    'prenom' => $permission->user->personne->prenom ?? '',
                                    'email' => $permission->user->email,
                                ],
                                'permission_type' => $permission->permission_type,
                                'expires_at' => $permission->expires_at?->format('Y-m-d H:i:s'),
                                'granted_at' => $permission->created_at->format('Y-m-d H:i:s'),
                            ];
                        });
                }
            ),

            // Métadonnées
            'metadata' => $this->metadata,
            'hash_md5' => $this->hash_md5,

            // Horodatage
            'created_at' => Carbon::parse($this->created_at)?->format("Y-m-d H:i:s"),
            'updated_at' => Carbon::parse($this->updated_at)?->format("Y-m-d H:i:s"),
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

        // Propriétaire peut voir
        if ($this->uploaded_by === $user->id) return true;

        // Fichier public
        if ($this->is_public) return true;

        // Vérifier permissions explicites
        if ($this->hasPermission($user, 'view')) return true;

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut télécharger le fichier
     */
    protected function canDownload(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Si peut voir, peut télécharger
        if ($this->canView()) return true;

        // Vérifier permission download explicite
        if ($this->hasPermission($user, 'download')) return true;

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer le fichier
     */
    protected function canDelete(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Propriétaire peut supprimer ses fichiers non attachés
        if ($this->uploaded_by === $user->id && !$this->fichier_attachable_id) {
            return true;
        }

        // Vérifier permission delete explicite
        if ($this->hasPermission($user, 'delete')) return true;

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut partager le fichier
     */
    protected function canShare(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Propriétaire peut partager
        if ($this->uploaded_by === $user->id) return true;

        // Vérifier permission share explicite
        if ($this->hasPermission($user, 'share')) return true;

        return false;
    }
}
