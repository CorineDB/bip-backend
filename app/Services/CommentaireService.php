<?php

namespace App\Services;

use App\Http\Resources\CommentaireResource;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CommentaireRepositoryInterface;
use App\Repositories\Contracts\FichierRepositoryInterface;
use App\Services\Contracts\CommentaireServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class CommentaireService extends BaseService implements CommentaireServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected CommentaireRepositoryInterface $commentaireRepository;
    protected FichierRepositoryInterface $fichierRepository;

    public function __construct(
        CommentaireRepositoryInterface $repository,
        FichierRepositoryInterface $fichierRepository
    )
    {
        parent::__construct($repository);
        $this->commentaireRepository = $repository;
        $this->fichierRepository = $fichierRepository;
    }

    protected function getResourceClass(): string
    {
        return CommentaireResource::class;
    }

    /**
     * Créer un commentaire avec possibilité d'attacher des fichiers
     *
     * @param array $data - Doit contenir:
     *   - commentaire: string (obligatoire)
     *   - commentaireable_type: string (obligatoire)
     *   - commentaireable_id: int (obligatoire)
     *   - commentaire_id: int (optionnel - pour les réponses)
     *   - fichiers: array (optionnel - fichiers uploadés)
     *
     * @return JsonResponse
     */
    public function create(array $data): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Valider les données obligatoires
            if (!isset($data['commentaire']) || empty(trim($data['commentaire']))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le commentaire ne peut pas être vide'
                ], 422);
            }

            if (!isset($data['commentaireable_type']) || !isset($data['commentaireable_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'La ressource commentée doit être spécifiée'
                ], 422);
            }

            // Préparer les données du commentaire
            $commentaireData = [
                'commentaire' => $data['commentaire'],
                'commentaireable_type' => $data['commentaireable_type'],
                'commentaireable_id' => $data['commentaireable_id'],
                'commentaire_id' => $data['commentaire_id'] ?? null,
                'commentateurId' => $user->id,
                'date' => now()
            ];

            // Créer le commentaire
            $commentaire = $this->commentaireRepository->create($commentaireData);

            // Gérer les fichiers attachés si présents
            if (isset($data['fichiers']) && is_array($data['fichiers']) && count($data['fichiers']) > 0) {
                $fichiersAttaches = $this->attacherFichiers($commentaire, $data['fichiers'], $user);

                Log::info('Fichiers attachés au commentaire', [
                    'commentaire_id' => $commentaire->id,
                    'nombre_fichiers' => count($fichiersAttaches),
                    'user_id' => $user->id
                ]);
            }

            DB::commit();

            // Recharger le commentaire avec ses relations
            $commentaire = $this->commentaireRepository->getInstance()
                ->with(['commentateur', 'fichiers.uploadedBy', 'enfants', 'parent'])
                ->find($commentaire->id);

            Log::info('Commentaire créé avec succès', [
                'commentaire_id' => $commentaire->id,
                'user_id' => $user->id,
                'has_fichiers' => $commentaire->fichiers->count() > 0
            ]);

            return response()->json([
                'success' => true,
                'data' => new CommentaireResource($commentaire),
                'message' => 'Commentaire créé avec succès'
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la création du commentaire', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse($e);
        }
    }

    /**
     * Mettre à jour un commentaire avec possibilité de gérer les fichiers
     *
     * @param int|string $id
     * @param array $data - Peut contenir:
     *   - commentaire: string (optionnel)
     *   - fichiers: array (optionnel - nouveaux fichiers à ajouter)
     *   - fichiers_a_supprimer: array (optionnel - IDs des fichiers à supprimer)
     *
     * @return JsonResponse
     */
    public function update(int|string $id, array $data): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $commentaire = $this->commentaireRepository->findOrFail($id);

            // Vérifier les permissions
            if ($commentaire->commentateurId !== $user->id && !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier ce commentaire'
                ], 403);
            }

            // Mettre à jour le texte du commentaire si fourni
            if (isset($data['commentaire'])) {
                $commentaire->commentaire = $data['commentaire'];
                $commentaire->save();
            }

            // Supprimer les fichiers demandés
            if (isset($data['fichiers_a_supprimer']) && is_array($data['fichiers_a_supprimer'])) {
                $this->supprimerFichiers($commentaire, $data['fichiers_a_supprimer'], $user);
            }

            // Ajouter de nouveaux fichiers
            if (isset($data['fichiers']) && is_array($data['fichiers']) && count($data['fichiers']) > 0) {
                $this->attacherFichiers($commentaire, $data['fichiers'], $user);
            }

            DB::commit();

            // Recharger le commentaire avec ses relations
            $commentaire = $this->commentaireRepository->getInstance()
                ->with(['commentateur', 'fichiers.uploadedBy', 'enfants', 'parent'])
                ->find($id);

            Log::info('Commentaire mis à jour avec succès', [
                'commentaire_id' => $commentaire->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => new CommentaireResource($commentaire),
                'message' => 'Commentaire mis à jour avec succès'
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la mise à jour du commentaire', [
                'commentaire_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e);
        }
    }

    /**
     * Supprimer un commentaire et ses fichiers attachés
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function delete(int|string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $commentaire = $this->commentaireRepository->findOrFail($id);

            // Vérifier les permissions
            if ($commentaire->commentateurId !== $user->id && !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer ce commentaire'
                ], 403);
            }

            // Supprimer tous les fichiers attachés
            $fichiers = $commentaire->fichiers;
            if ($fichiers->count() > 0) {
                $this->removeSpecificFiles($fichiers);
            }

            // Supprimer le commentaire
            $deleted = $this->commentaireRepository->delete($id);

            if (!$deleted) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Commentaire non trouvé'
                ], 404);
            }

            DB::commit();

            Log::info('Commentaire supprimé avec succès', [
                'commentaire_id' => $id,
                'user_id' => $user->id,
                'fichiers_supprimes' => $fichiers->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Commentaire supprimé avec succès'
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la suppression du commentaire', [
                'commentaire_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer tous les commentaires d'une ressource avec leurs fichiers
     *
     * @param string $resourceType
     * @param int $resourceId
     * @return JsonResponse
     */
    public function getCommentairesParRessource(string $resourceType, int $resourceId): JsonResponse
    {
        try {
            // Mapper le type court vers la classe complète si nécessaire
            $map = \App\Models\Commentaire::getCommentaireableMap();
            $fullResourceType = $map[strtolower($resourceType)] ?? $resourceType;

            $commentaires = $this->commentaireRepository->getInstance()
                ->where('commentaireable_type', $fullResourceType)
                ->where('commentaireable_id', $resourceId)
                ->whereNull('commentaire_id') // Seulement les commentaires racine
                ->with([
                    'commentateur',
                    'fichiers.uploadedBy',
                    'enfants.commentateur',
                    'enfants.fichiers.uploadedBy',
                    'enfants.parent'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => CommentaireResource::collection($commentaires),
                'total' => $commentaires->count()
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des commentaires', [
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e);
        }
    }

    /**
     * Attacher des fichiers à un commentaire
     *
     * @param \App\Models\Commentaire $commentaire
     * @param array $fichiers - Array de fichiers uploadés (UploadedFile)
     * @param \App\Models\User $user
     * @return array - Array des fichiers créés
     * @throws Exception
     */
    private function attacherFichiers($commentaire, array $fichiers, $user): array
    {
        $fichiersAttaches = [];

        foreach ($fichiers as $index => $file) {
            // Vérifier que c'est bien un fichier uploadé
            if (!is_object($file) || !method_exists($file, 'getClientOriginalName')) {
                Log::warning('Fichier invalide ignoré', [
                    'index' => $index,
                    'commentaire_id' => $commentaire->id
                ]);
                continue;
            }

            try {
                // Générer nom unique
                $nomStockage = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                // Déterminer le chemin de stockage
                $storagePath = "commentaires/{$commentaire->id}";

                // Stocker le fichier
                $cheminComplet = $file->storeAs($storagePath, $nomStockage, 'local');

                // Préparer les données du fichier
                $fichierData = [
                    'nom_original' => $file->getClientOriginalName(),
                    'nom_stockage' => $nomStockage,
                    'chemin' => $cheminComplet,
                    'extension' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType(),
                    'taille' => $file->getSize(),
                    'hash_md5' => md5_file(Storage::disk('local')->path($cheminComplet)),
                    'uploaded_by' => $user->id,
                    'is_active' => true,
                    'fichier_attachable_type' => get_class($commentaire),
                    'fichier_attachable_id' => $commentaire->id,
                    'categorie' => 'commentaire',
                    'ordre' => $index,
                    'metadata' => [
                        'upload_ip' => request()->ip(),
                        'upload_user_agent' => request()->userAgent(),
                        'upload_date' => now()->toISOString(),
                        'commentaire_id' => $commentaire->id,
                        'resource_type' => $commentaire->commentaireable_type,
                        'resource_id' => $commentaire->commentaireable_id
                    ]
                ];

                // Créer l'enregistrement du fichier
                $fichierCree = $this->fichierRepository->create($fichierData);
                $fichiersAttaches[] = $fichierCree;

            } catch (Exception $e) {
                // Supprimer le fichier physique si créé
                if (isset($cheminComplet) && Storage::disk('local')->exists($cheminComplet)) {
                    Storage::disk('local')->delete($cheminComplet);
                }

                Log::error('Erreur lors de l\'attachement d\'un fichier au commentaire', [
                    'commentaire_id' => $commentaire->id,
                    'fichier_index' => $index,
                    'error' => $e->getMessage()
                ]);

                // Continuer avec les autres fichiers
                continue;
            }
        }

        return $fichiersAttaches;
    }

    /**
     * Supprimer des fichiers spécifiques d'un commentaire
     *
     * @param \App\Models\Commentaire $commentaire
     * @param array $fichierIds - Array des IDs de fichiers à supprimer
     * @param \App\Models\User $user
     * @return void
     * @throws Exception
     */
    private function supprimerFichiers($commentaire, array $fichierIds, $user): void
    {
        foreach ($fichierIds as $fichierId) {
            try {
                $fichier = $this->fichierRepository->find($fichierId);

                if (!$fichier) {
                    continue;
                }

                // Vérifier que le fichier appartient bien au commentaire
                if ($fichier->fichier_attachable_id != $commentaire->id ||
                    $fichier->fichier_attachable_type != get_class($commentaire)) {
                    Log::warning('Tentative de suppression d\'un fichier non lié au commentaire', [
                        'fichier_id' => $fichierId,
                        'commentaire_id' => $commentaire->id,
                        'user_id' => $user->id
                    ]);
                    continue;
                }

                // Supprimer le fichier physique
                if (Storage::disk('local')->exists($fichier->chemin)) {
                    Storage::disk('local')->delete($fichier->chemin);
                }

                // Supprimer l'enregistrement
                $this->fichierRepository->delete($fichierId);

                Log::info('Fichier supprimé du commentaire', [
                    'fichier_id' => $fichierId,
                    'commentaire_id' => $commentaire->id,
                    'user_id' => $user->id
                ]);

            } catch (Exception $e) {
                Log::error('Erreur lors de la suppression d\'un fichier du commentaire', [
                    'fichier_id' => $fichierId,
                    'commentaire_id' => $commentaire->id,
                    'error' => $e->getMessage()
                ]);

                // Continuer avec les autres fichiers
                continue;
            }
        }
    }

    /**
     * Supprimer une liste spécifique de fichiers ou un seul fichier
     *
     * @param \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|array|\App\Models\Fichier $files
     * @return void
     */
    private function removeSpecificFiles($files): void
    {
        // Normaliser l'entrée en tableau
        if (!is_array($files) && !($files instanceof \Illuminate\Support\Collection)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            // Vérifier que c'est un objet Fichier valide
            if (!$file || !isset($file->chemin)) {
                continue;
            }

            // Supprimer le fichier physique du storage
            if (Storage::disk('local')->exists($file->chemin)) {
                Storage::disk('local')->delete($file->chemin);
            }

            // Supprimer l'enregistrement de la base de données
            if (isset($file->id)) {
                $this->fichierRepository->delete($file->id);
            }
        }
    }
}
