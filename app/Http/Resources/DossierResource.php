<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DossierResource extends BaseApiResource
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
            'nom' => $this->nom,
            'description' => $this->description,
            'path' => $this->path,
            'profondeur' => $this->profondeur,
            'parent_id' => $this->parent?->hashed_id,
            'couleur' => $this->couleur,
            'icone' => $this->icone,
            'is_public' => $this->is_public,

            // Relations - Ne pas inclure le parent complet pour éviter la récursion infinie
            // Le parent_id suffit pour la navigation, la hiérarchie complète est construite ailleurs
            /* 'parent' => $this->when($this->relationLoaded('parent') && $this->parent, function() {
                return new self($this->parent);
            }), */

            // Ne pas inclure les enfants non plus car la hiérarchie est déjà construite dans le service
            /* 'enfants' => $this->when($this->relationLoaded('children'), function() {
                return self::collection($this->children);
            }), */

            'created_by' => $this->whenLoaded('createdBy', function() {
                if (!$this->createdBy) return null;
                return [
                    'id' => $this->createdBy->hashed_id,
                    'nom' => $this->createdBy->personne->nom ?? null,
                    'email' => $this->createdBy->email
                ];
            }),

            // Fichiers du dossier
            'fichiers' => $this->when($this->relationLoaded('fichiers'), function() {
                return FichierResource::collection($this->fichiers);
            }),

            // Statistiques des fichiers
            'stats_fichiers' => $this->when($this->relationLoaded('fichiers'), function() {
                return [
                    'count' => $this->fichiers->count(),
                    'taille_totale' => $this->fichiers->sum('taille'),
                    'taille_formatee' => $this->formatBytes($this->fichiers->sum('taille')),
                    'derniere_modification' => $this->fichiers->max('updated_at') ?
                        Carbon::parse($this->fichiers->max('updated_at'))->format('d/m/Y H:i:s') : null
                ];
            }),

            // Navigation - Breadcrumb est construit dans le service pour éviter les récursions
            // 'breadcrumb' => $this->getBreadcrumb(),
            'full_path' => $this->full_path,

            // Permissions
            'permissions' => [
                'can_view' => $this->canView(),
                'can_edit' => $this->canEdit(),
                'can_delete' => $this->canDelete(),
                'can_create_subfolder' => $this->canCreateSubfolder(),
                'can_upload' => $this->canUpload()
            ],

            // Horodatage
            'created_at' => Carbon::parse($this->created_at)->format("d/m/Y H:i:s"),
            'updated_at' => Carbon::parse($this->updated_at)->format("d/m/Y H:i:s"),
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
                'type' => 'dossier',
                'version' => '1.0',
                'structure_info' => [
                    'max_profondeur' => 10,
                    'couleurs_disponibles' => [
                        '#2563EB' => 'Bleu',
                        '#059669' => 'Vert',
                        '#DC2626' => 'Rouge',
                        '#7C3AED' => 'Violet',
                        '#EA580C' => 'Orange',
                        '#0891B2' => 'Cyan',
                        '#6B7280' => 'Gris'
                    ],
                    'icones_disponibles' => [
                        'folder-open', 'folder', 'chart-bar', 'fire',
                        'adjustments', 'document-text', 'archive', 'collection'
                    ]
                ]
            ],
        ]);
    }

    /**
     * Formater les bytes en format lisible
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Vérifier si l'utilisateur peut voir le dossier
     */
    protected function canView(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Créateur peut voir
        if ($this->created_by === $user->id) return true;

        // Dossier public
        if ($this->is_public) return true;

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut modifier le dossier
     */
    protected function canEdit(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Créateur peut modifier
        if ($this->created_by === $user->id) return true;

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer le dossier
     */
    protected function canDelete(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Créateur peut supprimer si dossier vide
        if ($this->created_by === $user->id) {
            // Vérifier que le dossier est vide (pas de fichiers ni de sous-dossiers)
            $hasFichiers = $this->relationLoaded('fichiers') ?
                $this->fichiers->count() > 0 :
                $this->fichiers()->count() > 0;

            $hasEnfants = $this->relationLoaded('children') ?
                $this->children->count() > 0 :
                $this->children()->count() > 0;

            return !$hasFichiers && !$hasEnfants;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut créer un sous-dossier
     */
    protected function canCreateSubfolder(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Créateur peut créer des sous-dossiers si la profondeur max n'est pas atteinte
        if ($this->created_by === $user->id) {
            return $this->profondeur < 10; // Limite de profondeur
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut uploader des fichiers
     */
    protected function canUpload(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Créateur peut uploader
        if ($this->created_by === $user->id) return true;

        // Dans les dossiers publics selon les règles métier
        if ($this->is_public) {
            // TODO: Implémenter selon les règles spécifiques
            return false; // Prudent par défaut
        }

        return false;
    }
}
