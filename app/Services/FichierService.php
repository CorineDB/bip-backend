<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Http\Resources\FichierResource;
use App\Http\Resources\DossierResource;
use App\Repositories\Contracts\FichierRepositoryInterface;
use App\Services\Contracts\FichierServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
use App\Models\Fichier;
use App\Models\User;
use App\Models\Dossier;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FichierService extends BaseService implements FichierServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected FichierRepositoryInterface $fichierRepository;

    public function __construct(
        FichierRepositoryInterface $repository,
        FichierRepositoryInterface $fichierRepository
    )
    {
        parent::__construct($repository, $fichierRepository);
        $this->fichierRepository = $fichierRepository;
    }

    protected function getResourceClass(): string
    {
        return FichierResource::class;
    }

    /**
     * Réécriture de all() avec permissions et filtres, groupement par dossier
     */
    public function all(array $filters = []): JsonResponse
    {
        try {
            $user = Auth::user();

            // Mes fichiers (privés seulement)
            $mesFichiersQuery = Fichier::query()
                ->with(['uploadedBy', 'permissions', 'dossier'])
                ->where('uploaded_by', $user->id)
                ->where('is_public', false);

            // Fichiers partagés avec moi (privés avec permissions)
            $fichiersPartagesQuery = Fichier::query()
                ->with(['uploadedBy', 'permissions.grantedBy', 'dossier'])
                ->where('uploaded_by', '!=', $user->id)
                ->where('is_public', false)
                ->whereHas('permissions', function($permQuery) use ($user) {
                    $permQuery->active()->forUser($user->id);
                });

            // Fichiers publics (accessibles à tous)
            $fichiersPublicsQuery = Fichier::query()
                ->with(['uploadedBy', 'dossier'])
                ->where('is_public', true);

            // Appliquer les filtres communs aux trois catégories
            $queries = [$mesFichiersQuery, $fichiersPartagesQuery, $fichiersPublicsQuery];

            foreach ($queries as $query) {
                if (!empty($filters['dossier_id'])) {
                    if ($filters['dossier_id'] === 'null') {
                        $query->whereNull('dossier_id');
                    } else {
                        $query->where('dossier_id', $filters['dossier_id']);
                    }
                }

                if (!empty($filters['extension'])) {
                    $query->where('extension', $filters['extension']);
                }

                if (!empty($filters['search'])) {
                    $searchTerm = '%' . $filters['search'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('nom_original', 'ILIKE', $searchTerm)
                          ->orWhere('description', 'ILIKE', $searchTerm);
                    });
                }
            }

            // Exécuter les requêtes
            $mesFichiers = $mesFichiersQuery
                ->orderBy('created_at', 'desc')
                ->get();

            $fichiersPartages = $fichiersPartagesQuery
                ->orderBy('created_at', 'desc')
                ->get();

            $fichiersPublics = $fichiersPublicsQuery
                ->orderBy('created_at', 'desc')
                ->get();

            // Grouper par dossier si demandé
            $groupByFolder = $filters['group_by_folder'] ?? true;
            $data = [];

            if ($groupByFolder) {
                $data = [
                    'mes_fichiers' => $this->groupFichiersByDossierAvecProfondeur($mesFichiers, $user),
                    'fichiers_partages' => $this->groupFichiersByDossierAvecProfondeur($fichiersPartages, $user),
                    'public' => $this->groupFichiersByDossierAvecProfondeur($fichiersPublics, $user),
                    'structure_dossiers' => $this->getFolderStructure($user),
                ];

                dd($data);
            } else {
                $data = [
                    'mes_fichiers' => FichierResource::collection($mesFichiers),
                    'fichiers_partages' => FichierResource::collection($fichiersPartages),
                    'public' => FichierResource::collection($fichiersPublics),
                ];
            }

            // Statistiques
            $stats = [
                'mes_fichiers_count' => $mesFichiers->count(),
                'mes_fichiers_size' => $mesFichiers->sum('taille'),
                'mes_fichiers_size_formatted' => $this->formatBytes($mesFichiers->sum('taille')),

                'fichiers_partages_count' => $fichiersPartages->count(),
                'fichiers_partages_size' => $fichiersPartages->sum('taille'),
                'fichiers_partages_size_formatted' => $this->formatBytes($fichiersPartages->sum('taille')),

                'public_count' => $fichiersPublics->count(),
                'public_size' => $fichiersPublics->sum('taille'),
                'public_size_formatted' => $this->formatBytes($fichiersPublics->sum('taille')),

                'total_count' => $mesFichiers->count() + $fichiersPartages->count() + $fichiersPublics->count(),
                'total_size' => $mesFichiers->sum('taille') + $fichiersPartages->sum('taille') + $fichiersPublics->sum('taille'),
                'total_size_formatted' => $this->formatBytes($mesFichiers->sum('taille') + $fichiersPartages->sum('taille') + $fichiersPublics->sum('taille')),

                'dossiers_count' => $groupByFolder ? $this->countActiveFolders($user) : 0
            ];

            $data['stats'] = $stats;

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Fichiers récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtenir uniquement les fichiers publics
     */
    public function getFichiersPublics(array $filters = []): JsonResponse
    {
        try {
            $user = Auth::user();

            // Fichiers publics seulement
            $fichiersPublicsQuery = Fichier::query()
                ->with(['uploadedBy', 'dossier'])
                ->where('is_public', true);

            // Appliquer les filtres
            if (!empty($filters['dossier_id'])) {
                if ($filters['dossier_id'] === 'null') {
                    $fichiersPublicsQuery->whereNull('dossier_id');
                } else {
                    $fichiersPublicsQuery->where('dossier_id', $filters['dossier_id']);
                }
            }

            if (!empty($filters['extension'])) {
                $fichiersPublicsQuery->where('extension', $filters['extension']);
            }

            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $fichiersPublicsQuery->where(function($q) use ($searchTerm) {
                    $q->where('nom_original', 'ILIKE', $searchTerm)
                      ->orWhere('description', 'ILIKE', $searchTerm);
                });
            }

            $fichiersPublics = $fichiersPublicsQuery
                ->orderBy('created_at', 'desc')
                ->get();

            $groupByFolder = $filters['group_by_folder'] ?? false;

            $data = $groupByFolder ?
                $this->groupFichiersByDossierAvecProfondeur($fichiersPublics, $user) :
                FichierResource::collection($fichiersPublics);

            return response()->json([
                'success' => true,
                'data' => [
                    'public' => $data,
                    'stats' => [
                        'count' => $fichiersPublics->count(),
                        'taille_totale' => $fichiersPublics->sum('taille'),
                        'taille_formatee' => $this->formatBytes($fichiersPublics->sum('taille'))
                    ]
                ],
                'message' => 'Fichiers publics récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtenir tous les fichiers groupés par dossier avec structure hiérarchique
     */
    public function getAllGroupedByFolder(array $filters = []): JsonResponse
    {
        $filters['group_by_folder'] = true;
        return $this->all($filters);
    }

    /**
     * Obtenir un dossier avec ses fichiers et permissions
     */
    public function getFichiersByFolder(int $dossierId, array $filters = []): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier que le dossier existe et est accessible
            $dossierQuery = Dossier::where('id', $dossierId)
                ->where(function($q) use ($user) {
                    $q->where('is_public', true)
                      ->orWhere('created_by', $user->id);
                });

            // Charger le dossier avec ses fichiers filtrés
            $dossier = $dossierQuery->with([
                'createdBy',
                'fichiers' => function($query) use ($user, $filters) {
                    // Appliquer les permissions sur les fichiers
                    $query->where(function($q) use ($user) {
                        $q->where('uploaded_by', $user->id)
                          ->orWhere('is_public', true);
                    });

                    // Appliquer les filtres
                    if (!empty($filters['extension'])) {
                        $query->where('extension', $filters['extension']);
                    }

                    if (!empty($filters['search'])) {
                        $searchTerm = '%' . $filters['search'] . '%';
                        $query->where(function($q) use ($searchTerm) {
                            $q->where('nom_original', 'ILIKE', $searchTerm)
                              ->orWhere('description', 'ILIKE', $searchTerm);
                        });
                    }

                    $query->orderBy('created_at', 'desc');
                },
                'children' // Pour les sous-dossiers si nécessaire
            ])->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => new DossierResource($dossier),
                'message' => 'Dossier et ses fichiers récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtenir tous les dossiers avec leurs fichiers (structure complète)
     */
    public function getAllDossiersWithFichiers(array $filters = []): JsonResponse
    {
        try {
            $user = Auth::user();

            // Récupérer tous les dossiers accessibles avec leurs fichiers
            $dossiers = Dossier::where(function($q) use ($user) {
                    $q->where('is_public', true)
                      ->orWhere('created_by', $user->id);
                })
                ->with([
                    'createdBy',
                    'fichiers' => function($query) use ($user, $filters) {
                        // Appliquer les permissions
                        $query->where(function($q) use ($user) {
                            $q->where('uploaded_by', $user->id)
                              ->orWhere('is_public', true);
                        });

                        // Appliquer les filtres si fournis
                        if (!empty($filters['extension'])) {
                            $query->where('extension', $filters['extension']);
                        }
                        if (!empty($filters['search'])) {
                            $searchTerm = '%' . $filters['search'] . '%';
                            $query->where(function($q) use ($searchTerm) {
                                $q->where('nom_original', 'ILIKE', $searchTerm)
                                  ->orWhere('description', 'ILIKE', $searchTerm);
                            });
                        }

                        $query->orderBy('created_at', 'desc');
                    }
                ])
                ->orderBy('profondeur')
                ->orderBy('nom')
                ->get();

            // Fichiers sans dossier
            $fichiersSansDossier = $this->buildPermissionsQuery($user)
                ->whereNull('dossier_id');

            // Appliquer les filtres aux fichiers sans dossier
            if (!empty($filters['extension'])) {
                $fichiersSansDossier->where('extension', $filters['extension']);
            }
            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $fichiersSansDossier->where(function($q) use ($searchTerm) {
                    $q->where('nom_original', 'ILIKE', $searchTerm)
                      ->orWhere('description', 'ILIKE', $searchTerm);
                });
            }

            $fichiersSansDossier = $fichiersSansDossier->orderBy('created_at', 'desc')->get();

            $data = [
                'dossiers' => DossierResource::collection($dossiers),
                'fichiers_sans_dossier' => [
                    'type' => 'sans_dossier',
                    'nom_dossier' => 'Fichiers sans dossier',
                    'description' => 'Fichiers qui ne sont pas classés dans un dossier',
                    'couleur' => '#6B7280',
                    'icone' => 'folder',
                    'fichiers' => FichierResource::collection($fichiersSansDossier),
                    'count' => $fichiersSansDossier->count(),
                    'taille_totale' => $fichiersSansDossier->sum('taille'),
                    'taille_formatee' => $this->formatBytes($fichiersSansDossier->sum('taille'))
                ],
                'structure_hierarchique' => $this->buildFolderTreeWithResource($dossiers)
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Tous les dossiers avec fichiers récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Réécriture de find() avec vérification des permissions
     */
    public function find(int|string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $fichier = $this->repository->findOrFail($id);

            // Vérifier les permissions
            if (!$this->aPermissionSurFichier($user, $fichier, 'view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour accéder à ce fichier'
                ], 403);
            }

            // Logger l'accès
            $this->logAccesFichier($fichier, 'view_details');

            return response()->json([
                'success' => true,
                'data' => new FichierResource($fichier->load(['uploadedBy', 'fichierAttachable'])),
                'message' => 'Fichier trouvé avec succès'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Réécriture de create() pour les fichiers attachés à des ressources
     */
    public function create(array $data): JsonResponse
    {
        // Validation des données de base
        if (!isset($data['fichier']) && !isset($data['chemin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier manquant dans les données'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Si c'est un fichier uploadé (avec Request file)
            if (isset($data['fichier']) && is_object($data['fichier'])) {
                $file = $data['fichier'];

                // Générer nom unique
                $nomStockage = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                // Déterminer le chemin selon le type de ressource
                $storagePath = $this->determinerCheminStockage($data);

                // Stocker le fichier
                $cheminComplet = $file->storeAs($storagePath, $nomStockage, 'local');

                // Préparer les données complètes
                $fichierData = array_merge($data, [
                    'nom_original' => $file->getClientOriginalName(),
                    'nom_stockage' => $nomStockage,
                    'chemin' => $cheminComplet,
                    'extension' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType(),
                    'taille' => $file->getSize(),
                    'hash_md5' => md5_file(Storage::disk('local')->path($cheminComplet)),
                    'uploaded_by' => $user->id,
                    'is_active' => true,
                    'metadata' => array_merge([
                        'upload_ip' => request()->ip(),
                        'upload_user_agent' => request()->userAgent(),
                        'upload_date' => now()->toISOString(),
                        'resource_attachment' => true
                    ], $data['metadata'] ?? [])
                ]);
            } else {
                // Fichier déjà traité (ex: migration, import)
                $fichierData = array_merge($data, [
                    'uploaded_by' => $data['uploaded_by'] ?? $user->id,
                    'is_active' => $data['is_active'] ?? true
                ]);
            }

            // Vérifier les permissions sur la ressource attachée
            if (isset($fichierData['fichier_attachable_type']) && isset($fichierData['fichier_attachable_id'])) {
                if (!$this->peutAttacherFichierAResource($user, $fichierData['fichier_attachable_type'], $fichierData['fichier_attachable_id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vous n\'avez pas les permissions pour attacher un fichier à cette ressource'
                    ], 403);
                }
            }

            $fichier = $this->repository->create($fichierData);

            DB::commit();

            Log::info('Fichier attaché créé avec succès', [
                'user_id' => $user->id,
                'fichier_id' => $fichier->id,
                'resource_type' => $fichierData['fichier_attachable_type'] ?? null,
                'resource_id' => $fichierData['fichier_attachable_id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'data' => new FichierResource($fichier),
                'message' => 'Fichier créé et attaché avec succès'
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            // Supprimer le fichier physique si créé
            if (isset($cheminComplet) && Storage::disk('local')->exists($cheminComplet)) {
                Storage::disk('local')->delete($cheminComplet);
            }

            Log::error('Erreur lors de la création du fichier attaché', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return $this->errorResponse($e);
        }
    }

    /**
     * Réécriture de update() avec permissions
     */
    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            $user = Auth::user();
            $fichier = $this->repository->findOrFail($id);

            // Vérifier les permissions de modification
            if (!$this->peutModifierFichier($user, $fichier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier ce fichier'
                ], 403);
            }

            // Filtrer les données modifiables (sécurité)
            $updateableData = $this->filtrerDonneesModifiables($data, $user, $fichier);

            $updated = $this->repository->update($id, $updateableData);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé ou non modifié'
                ], 404);
            }

            $fichier = $this->repository->findOrFail($id);

            Log::info('Fichier modifié avec succès', [
                'user_id' => $user->id,
                'fichier_id' => $id,
                'updated_fields' => array_keys($updateableData)
            ]);

            return response()->json([
                'success' => true,
                'data' => new FichierResource($fichier),
                'message' => 'Fichier modifié avec succès'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Réécriture de delete() avec permissions et gestion des fichiers attachés
     */
    public function delete(int|string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $fichier = $this->repository->findOrFail($id);

            // Vérifier les permissions de suppression
            if (!$this->peutSupprimerFichier($user, $fichier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer ce fichier'
                ], 403);
            }

            // Vérifier si le fichier est attaché à une ressource critique
            if ($fichier->fichier_attachable_id && !$this->peutSupprimerFichierAttache($user, $fichier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce fichier ne peut pas être supprimé car il est attaché à une ressource'
                ], 400);
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé ou non supprimé'
                ], 404);
            }

            Log::info('Fichier supprimé avec succès', [
                'user_id' => $user->id,
                'fichier_id' => $id,
                'nom_original' => $fichier->nom_original,
                'was_attached' => !is_null($fichier->fichier_attachable_id)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fichier supprimé avec succès'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les fichiers accessibles à l'utilisateur groupés par dossier
     */
    public function getFichiersAccessibles(array $filters = []): JsonResponse
    {
        try {
            $user = Auth::user();

            // Construire la requête de base avec les permissions
            $query = $this->buildPermissionsQuery($user);

            // Appliquer les filtres
            if (!empty($filters['dossier'])) {
                $query->where('categorie', $filters['dossier']);
            }

            if (!empty($filters['type'])) {
                $query->where('mime_type', 'like', $filters['type'] . '%');
            }

            if (!empty($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('nom_original', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            $fichiers = $query->with(['uploadedBy'])
                            ->orderBy('categorie')
                            ->orderBy('created_at', 'desc')
                            ->get();

            // Grouper par dossier si demandé
            $result = $filters['grouper_par_dossier'] ?? false
                ? $this->grouperParDossier($fichiers)
                : $fichiers;

            return response()->json([
                'success' => true,
                'data' => FichierResource::collection($fichiers),
                'grouped' => $result,
                'total' => $fichiers->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des fichiers accessibles', [
                'user_id' => Auth::id(),
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des fichiers'
            ], 500);
        }
    }

    /**
     * Upload d'un fichier libre (sans ressource rattachée)
     */
    public function uploadFichierLibre(Request $request): JsonResponse
    {
        $request->validate([
            'fichier' => 'required|file|max:20480', // 20MB max
            'categorie' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
            'metadata' => 'nullable|array'
        ]);

        DB::beginTransaction();

        try {
            $file = $request->file('fichier');
            $user = Auth::user();

            // Générer un nom unique
            $nomStockage = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Définir le chemin de stockage
            $categorie = $request->get('categorie', 'uploads');
            $storagePath = "fichiers/{$user->id}/{$categorie}";

            // Stocker le fichier
            $cheminComplet = $file->storeAs($storagePath, $nomStockage, 'local');

            // Calculer le hash MD5
            $hashMd5 = md5_file(Storage::disk('local')->path($cheminComplet));

            // Créer l'enregistrement
            $fichierData = [
                'nom_original' => $file->getClientOriginalName(),
                'nom_stockage' => $nomStockage,
                'chemin' => $cheminComplet,
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'taille' => $file->getSize(),
                'hash_md5' => $hashMd5,
                'description' => $request->get('description'),
                'categorie' => $categorie,
                'uploaded_by' => $user->id,
                'is_public' => $request->boolean('is_public', false),
                'is_active' => true,
                'metadata' => array_merge([
                    'upload_ip' => $request->ip(),
                    'upload_user_agent' => $request->userAgent(),
                    'upload_date' => now()->toISOString()
                ], $request->get('metadata', []))
            ];

            $fichier = $this->repository->create($fichierData);

            DB::commit();

            Log::info('Fichier libre uploadé avec succès', [
                'user_id' => $user->id,
                'fichier_id' => $fichier->id,
                'nom_original' => $file->getClientOriginalName(),
                'categorie' => $categorie
            ]);

            return response()->json([
                'success' => true,
                'data' => new FichierResource($fichier),
                'message' => 'Fichier uploadé avec succès'
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            // Supprimer le fichier physique si créé
            if (isset($cheminComplet) && Storage::disk('local')->exists($cheminComplet)) {
                Storage::disk('local')->delete($cheminComplet);
            }

            Log::error('Erreur lors de l\'upload du fichier libre', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload du fichier'
            ], 500);
        }
    }

    /**
     * Télécharger un fichier avec vérification des permissions par hash
     */
    public function telechargerFichier(string $hash): StreamedResponse
    {
        $fichier = $this->getFichierAvecPermissionsParHash($hash);

        if (!$fichier) {
            throw new ModelNotFoundException("Fichier non trouvé", 1);
        }

        if (!Storage::disk('local')->exists($fichier->chemin)) {
            throw new ModelNotFoundException("Fichier physique n'a pas été trouvé", 1);
        }

        // Logger et incrémenter compteur
        $this->logAccesFichier($fichier, 'download');
        //$this->incrementerTelechargements($fichier->id);

        return Storage::disk('local')->download($fichier->chemin, $fichier->nom_original);
    }

    /**
     * Visualiser un fichier dans le navigateur par hash
     */
    public function visualiserFichier(string $hash): StreamedResponse
    {
        $fichier = $this->getFichierAvecPermissionsParHash($hash);

        if (!$fichier) {
            throw new ModelNotFoundException("Fichier non trouvé", 1);
        }

        if (!Storage::disk('local')->exists($fichier->chemin)) {
            throw new ModelNotFoundException("Fichier physique n'a pas été trouvé", 1);
        }

        // Logger et incrémenter compteur
        $this->logAccesFichier($fichier, 'view');
        //$this->incrementerVues($fichier->id);

        $headers = [
            'Content-Type' => $fichier->mime_type,
            'Content-Disposition' => 'inline; filename="' . $fichier->nom_original . '"',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        return response()->stream(function() use ($fichier) {
            $stream = Storage::disk('local')->readStream($fichier->chemin);
            if ($stream === false) {
                abort(500, "Impossible de lire le fichier");
            }
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }

    /**
     * Supprimer un fichier (seulement les fichiers uploadés librement)
     */
    public function supprimerFichier(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $fichier = $this->repository->find($id);

            if (!$fichier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé'
                ], 404);
            }

            // Vérifier que l'utilisateur peut supprimer ce fichier
            if (!$this->peutSupprimerFichier($user, $fichier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer ce fichier'
                ], 403);
            }

            // Supprimer le fichier (le model se charge de supprimer le fichier physique)
            $this->repository->delete($id);

            Log::info('Fichier supprimé avec succès', [
                'user_id' => $user->id,
                'fichier_id' => $id,
                'nom_original' => $fichier->nom_original
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fichier supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de fichier', [
                'user_id' => Auth::id(),
                'fichier_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du fichier'
            ], 500);
        }
    }

    /**
     * Vérifier les permissions d'accès à un fichier
     */
    public function verifierPermissionsFichier(string $id, string $permission = 'view'): bool
    {
        $user = Auth::user();
        $fichier = $this->repository->find($id);

        if (!$fichier) {
            return false;
        }

        return $this->aPermissionSurFichier($user, $fichier, $permission);
    }

    /**
     * Obtenir un fichier avec vérification des permissions
     */
    public function getFichierAvecPermissions(string $id): ?object
    {
        $user = Auth::user();
        $fichier = $this->repository->find($id);

        /* if (!$fichier || !$this->aPermissionSurFichier($user, $fichier, 'view')) {
            return null;
        } */

        return $fichier;
    }

    /**
     * Obtenir un fichier avec vérification des permissions par hash MD5
     */
    public function getFichierAvecPermissionsParHash(string $hash): ?object
    {
        $user = Auth::user();
        $fichier = Fichier::where('hash_md5', $hash)->latest('created_at')->first();

        /* if (!$fichier || !$this->aPermissionSurFichier($user, $fichier, 'view')) {
            return null;
        } */

        return $fichier;
    }

    /**
     * Incrémenter le compteur de téléchargements
     */
    public function incrementerTelechargements(string $id): void
    {
        $this->repository->increment($id, 'nb_telechargements', 1);
    }

    /**
     * Incrémenter le compteur de vues
     */
    public function incrementerVues(string $id): void
    {
        $this->repository->increment($id, 'nb_vues', 1);
    }

    // Méthodes privées

    /**
     * Construire la requête avec les permissions utilisateur
     */
    private function buildPermissionsQuery(User $user)
    {
        $query = Fichier::query();

        // Admin voit tout
        if ($user->hasRole('admin')) {
            return $query;
        }

        // Si l'utilisateur est de la DGPD, il voit tous les fichiers
        if ($user->profilable_type === 'App\Models\Dgpd') {
            return $query;
        }

        // Sinon filtrer selon les permissions
        return $query->where(function($q) use ($user) {
            $q->where('uploaded_by', $user->id) // Ses propres fichiers
              ->orWhere('is_public', true) // Fichiers publics
              ->orWhereHas('fichierAttachable', function($subQ) use ($user) {
                  // Fichiers attachés aux ressources accessibles du même ministère
                  $this->applyResourcePermissions($subQ, $user);
              });
        });
    }

    /**
     * Vérifier si l'utilisateur a permission sur un fichier
     */
    private function aPermissionSurFichier(User $user, $fichier, string $permission): bool
    {
        // Admin a toutes les permissions
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Si l'utilisateur est de la DGPD, il a accès à tous les fichiers
        if ($user->profilable_type === 'App\Models\Dgpd') {
            return true;
        }

        // Propriétaire a toutes les permissions
        if ($fichier->uploaded_by === $user->id) {
            return true;
        }

        // Fichier public (view seulement)
        if ($fichier->is_public && $permission === 'view') {
            return true;
        }

        // Vérifier permissions sur ressource attachée selon le ministère
        if ($fichier->fichier_attachable_type && $fichier->fichier_attachable_id) {
            return $this->aPermissionSurRessourceAttachee($user, $fichier);
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur a permission sur une ressource attachée selon son ministère
     */
    private function aPermissionSurRessourceAttachee(User $user, $fichier): bool
    {
        $userMinistereId = $user->profilable?->ministere_id ?? null;

        if (!$userMinistereId) {
            return false; // Si l'utilisateur n'a pas de ministère, pas d'accès
        }

        $resourceType = $fichier->fichier_attachable_type;
        $resourceId = $fichier->fichier_attachable_id;

        switch ($resourceType) {
            case 'App\Models\Projet':
            case 'App\Models\IdeeProjet':
                // Vérifier directement sur le projet/idée de projet
                $resource = app($resourceType)->find($resourceId);
                return $resource && $resource->ministereId === $userMinistereId;

            case 'App\Models\NoteConceptuelle':
                // Vérifier via le projet associé à la note conceptuelle
                $noteConceptuelle = app($resourceType)->with('projet')->find($resourceId);
                return $noteConceptuelle &&
                       $noteConceptuelle->projet &&
                       $noteConceptuelle->projet->ministereId === $userMinistereId;

            case 'App\Models\Tdr':
            case 'App\Models\Rapport':
                // Vérifier via le projet associé au TDR/rapport
                $resource = app($resourceType)->with('projet')->find($resourceId);
                return $resource &&
                       $resource->projet &&
                       $resource->projet->ministereId === $userMinistereId;

            default:
                return false; // Type de ressource non géré
        }
    }

    /**
     * Vérifier si l'utilisateur peut supprimer un fichier
     */
    private function peutSupprimerFichier(User $user, $fichier): bool
    {
        // Admin peut tout supprimer
        if ($user->hasRole('admin')) {
            return true;
        }

        // Propriétaire peut supprimer ses fichiers non attachés
        if ($fichier->uploaded_by === $user->id && !$fichier->fichier_attachable_id) {
            return true;
        }

        return false;
    }

    /**
     * Logger l'accès à un fichier
     */
    private function logAccesFichier($fichier, string $action): void
    {
        Log::info("Accès fichier: {$action}", [
            'user_id' => Auth::id(),
            'fichier_id' => $fichier->id,
            'nom_original' => $fichier->nom_original,
            'action' => $action,
            'ip' => request()->ip()
        ]);
    }

    /**
     * Grouper les fichiers par dossier
     */
    private function grouperParDossier($fichiers): array
    {
        return $fichiers->groupBy('categorie')->map(function ($fichiers, $categorie) {
            return [
                'categorie' => $categorie ?: 'Sans catégorie',
                'fichiers' => FichierResource::collection($fichiers),
                'total' => $fichiers->count(),
                'taille_totale' => $fichiers->sum('taille')
            ];
        })->values()->toArray();
    }

    /**
     * Appliquer les permissions sur les ressources attachées selon le ministère de l'utilisateur
     */
    private function applyResourcePermissions($query, User $user): void
    {
        $userMinistereId = $user->profilable->ministere_id ?? null;

        if (!$userMinistereId) {
            return; // Si l'utilisateur n'a pas de ministère, pas d'accès
        }

        $query->where(function($q) use ($userMinistereId) {
            // Fichiers attachés aux projets du même ministère
            $q->orWhere(function($subQ) use ($userMinistereId) {
                $subQ->where('fichier_attachable_type', 'App\Models\Projet')
                     ->whereHas('fichierAttachable', function($projetQuery) use ($userMinistereId) {
                         $projetQuery->where('ministere_id', $userMinistereId);
                     });
            })
            // Fichiers attachés aux idées de projet du même ministère
            ->orWhere(function($subQ) use ($userMinistereId) {
                $subQ->where('fichier_attachable_type', 'App\Models\IdeeProjet')
                     ->whereHas('fichierAttachable', function($ideeQuery) use ($userMinistereId) {
                         $ideeQuery->where('ministere_id', $userMinistereId);
                     });
            })
            // Fichiers attachés aux notes conceptuelles des projets du même ministère
            ->orWhere(function($subQ) use ($userMinistereId) {
                $subQ->where('fichier_attachable_type', 'App\Models\NoteConceptuelle')
                     ->whereHas('fichierAttachable.projet', function($projetQuery) use ($userMinistereId) {
                         $projetQuery->where('ministere_id', $userMinistereId);
                     });
            })
            // Fichiers attachés aux TDR des projets du même ministère
            ->orWhere(function($subQ) use ($userMinistereId) {
                $subQ->where('fichier_attachable_type', 'App\Models\Tdr')
                     ->whereHas('fichierAttachable.projet', function($projetQuery) use ($userMinistereId) {
                         $projetQuery->where('ministere_id', $userMinistereId);
                     });
            })
            // Fichiers attachés aux rapports des projets du même ministère
            ->orWhere(function($subQ) use ($userMinistereId) {
                $subQ->where('fichier_attachable_type', 'App\Models\Rapport')
                     ->whereHas('fichierAttachable.projet', function($projetQuery) use ($userMinistereId) {
                         $projetQuery->where('ministere_id', $userMinistereId);
                     });
            });
        });
    }

    /**
     * Déterminer le chemin de stockage selon le type de ressource
     */
    private function determinerCheminStockage(array $data): string
    {
        $user = Auth::user();
        $basePath = "fichiers/{$user->id}";

        // Si attaché à une ressource spécifique
        if (isset($data['fichier_attachable_type']) && isset($data['fichier_attachable_id'])) {
            $type = strtolower(class_basename($data['fichier_attachable_type']));
            return "{$basePath}/{$type}/{$data['fichier_attachable_id']}";
        }

        // Si une catégorie est spécifiée
        if (isset($data['categorie'])) {
            return "{$basePath}/{$data['categorie']}";
        }

        // Chemin par défaut
        return "{$basePath}/attachments";
    }

    /**
     * Vérifier si l'utilisateur peut attacher un fichier à une ressource
     */
    private function peutAttacherFichierAResource(User $user, string $resourceType, int $resourceId): bool
    {
        // Admin peut tout faire
        if ($user->hasRole('admin')) {
            return true;
        }

        // TODO: Implémenter selon les types de ressources
        // Pour l'instant, autoriser si l'utilisateur est propriétaire ou a les bonnes permissions

        return true; // Temporaire - à implémenter selon la logique métier
    }

    /**
     * Vérifier si l'utilisateur peut modifier un fichier
     */
    private function peutModifierFichier(User $user, $fichier): bool
    {
        // Admin peut tout modifier
        if ($user->hasRole('admin')) {
            return true;
        }

        // Propriétaire peut modifier ses fichiers
        if ($fichier->uploaded_by === $user->id) {
            return true;
        }

        // TODO: Vérifier permissions sur ressource attachée

        return false;
    }

    /**
     * Filtrer les données modifiables selon l'utilisateur et le fichier
     */
    private function filtrerDonneesModifiables(array $data, User $user, $fichier): array
    {
        $allowedFields = ['description', 'categorie', 'is_public', 'commentaire', 'metadata'];

        // Admin peut modifier plus de champs
        if ($user->hasRole('admin')) {
            $allowedFields = array_merge($allowedFields, ['is_active', 'ordre']);
        }

        // Filtrer les données selon les champs autorisés
        $filteredData = array_intersect_key($data, array_flip($allowedFields));

        // Sécurité : ne pas permettre de rendre public un fichier attaché sans permissions
        if (isset($filteredData['is_public']) && $filteredData['is_public'] && $fichier->fichier_attachable_id) {
            if (!$this->peutRendrePublicFichierAttache($user, $fichier)) {
                unset($filteredData['is_public']);
            }
        }

        return $filteredData;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer un fichier attaché
     */
    private function peutSupprimerFichierAttache(User $user, $fichier): bool
    {
        // Admin peut supprimer
        if ($user->hasRole('admin')) {
            return true;
        }

        // Propriétaire peut supprimer ses fichiers libres, mais pas les attachés critiques
        if ($fichier->uploaded_by === $user->id) {
            // TODO: Vérifier si la ressource attachée permet la suppression
            return false; // Prudent : ne pas permettre par défaut
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut rendre public un fichier attaché
     */
    private function peutRendrePublicFichierAttache(User $user, $fichier): bool
    {
        // Admin peut tout faire
        if ($user->hasRole('admin')) {
            return true;
        }

        // TODO: Implémenter selon la logique métier
        // Généralement, les fichiers attachés ne doivent pas être rendus publics

        return false;
    }

    /**
     * Obtenir les fichiers "Partagés avec moi" (équivalent Google Drive)
     */
    public function getFichiersPartagesAvecMoi(array $filters = []): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Fichier::query()
                ->with(['uploadedBy', 'permissions.grantedBy'])
                ->where('uploaded_by', '!=', $user->id) // Pas mes propres fichiers
                ->whereHas('permissions', function($permQuery) use ($user) {
                    $permQuery->active()->forUser($user->id);
                });

            // Appliquer filtres
            if (!empty($filters['permission_type'])) {
                $query->whereHas('permissions', function($permQuery) use ($user, $filters) {
                    $permQuery->active()
                             ->forUser($user->id)
                             ->byType($filters['permission_type']);
                });
            }

            if (!empty($filters['search'])) {
                $query->where('nom_original', 'ILIKE', '%' . $filters['search'] . '%');
            }

            $fichiers = $query->orderBy('created_at', 'desc')
                            ->paginate($filters['per_page'] ?? 20);

            return response()->json([
                'success' => true,
                'data' => FichierResource::collection($fichiers),
                'message' => 'Fichiers partagés récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Partager un fichier avec des utilisateurs spécifiques
     */
    public function partagerFichierAvecUtilisateurs(string $id, array $data): JsonResponse
    {
        try {
            $user = Auth::user();
            $fichier = $this->repository->findOrFail($id);

            // Vérifier permissions de partage
            if (!$this->peutPartagerFichier($user, $fichier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour partager ce fichier'
                ], 403);
            }

            $userIds = $data['user_ids'] ?? [];
            $permissions = $data['permissions'] ?? ['view'];
            $expiresAt = isset($data['expires_at']) ? new \Carbon\Carbon($data['expires_at']) : null;

            $results = [];
            foreach ($userIds as $userId) {
                $targetUser = User::find($userId);
                if ($targetUser) {
                    foreach ($permissions as $permission) {
                        $results[] = $fichier->grantPermission($targetUser, $permission, $user, $expiresAt);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Fichier partagé avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtenir la file d'attente des fichiers récents
     */
    public function getFileQueue(): JsonResponse
    {
        try {
            $user = Auth::user();

            $fichiers = Fichier::query()
                ->with(['uploadedBy'])
                ->where(function ($q) use ($user) {
                    $q->where('uploaded_by', $user->id)
                      ->orWhere('is_public', true)
                      ->orWhereHas('permissions', function($permQuery) use ($user) {
                          $permQuery->active()->forUser($user->id);
                      });
                })
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => FichierResource::collection($fichiers),
                'message' => 'File d\'attente récupérée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Vérifier si l'utilisateur peut partager un fichier
     */
    private function peutPartagerFichier(User $user, $fichier): bool
    {
        // Admin peut tout partager
        if ($user->hasRole('admin')) {
            return true;
        }

        // Propriétaire peut partager ses fichiers
        if ($fichier->uploaded_by === $user->id) {
            return true;
        }

        // Vérifier si l'utilisateur a la permission 'share'
        return $fichier->hasPermission($user, 'share');
    }

    /**
     * Grouper les fichiers par dossier avec structure hiérarchique
     */
    private function groupFichiersByFolder($fichiers): array
    {
        $grouped = [];

        // Grouper par dossier_id
        $fichiersGroupes = $fichiers->groupBy('dossier_id');

        foreach ($fichiersGroupes as $dossierId => $fichiersDuDossier) {
            if ($dossierId === null) {
                // Fichiers sans dossier
                $grouped['sans_dossier'] = [
                    'type' => 'sans_dossier',
                    'nom_dossier' => 'Fichiers sans dossier',
                    'path' => null,
                    'description' => 'Fichiers qui ne sont pas classés dans un dossier',
                    'couleur' => '#6B7280',
                    'icone' => 'folder',
                    'fichiers' => FichierResource::collection($fichiersDuDossier),
                    'count' => $fichiersDuDossier->count(),
                    'taille_totale' => $fichiersDuDossier->sum('taille'),
                    'taille_formatee' => $this->formatBytes($fichiersDuDossier->sum('taille'))
                ];
            } else {
                // Fichiers avec dossier - charger le dossier avec ses fichiers
                $dossier = Dossier::with(['fichiers', 'createdBy'])
                    ->find($dossierId);

                if ($dossier) {
                    $grouped['dossier_' . $dossierId] = new DossierResource($dossier);
                }
            }
        }

        return $grouped;
    }

    /**
     * Grouper les fichiers par dossier avec structure hiérarchique complète
     */
    private function groupFichiersByDossierAvecProfondeur($fichiers, User $user): array
    {
        $result = [];

        // 1. Récupérer tous les dossiers concernés et leurs parents
        $dossierIds = $fichiers->whereNotNull('dossier_id')->pluck('dossier_id')->unique();
        $todosDossiers = collect();

        // Récupérer tous les dossiers et remonter jusqu'aux racines
        foreach ($dossierIds as $dossierId) {
            $dossier = Dossier::with(['parent', 'createdBy'])->find($dossierId);
            if ($dossier) {
                // Ajouter le dossier et tous ses parents
                $current = $dossier;
                while ($current) {
                    if (!$todosDossiers->contains('id', $current->id)) {
                        $todosDossiers->push($current);
                    }
                    $current = $current->parent;
                }
            }
        }

        // 2. Grouper les fichiers par dossier
        $fichiersParDossier = $fichiers->groupBy('dossier_id');

        // 3. Construire la structure hiérarchique
        $dossiersRacine = $todosDossiers->where('parent_id', null);

        foreach ($dossiersRacine as $dossierRacine) {
            $result[$dossierRacine->id] = $this->buildDossierHierarchyWithFiles(
                $dossierRacine,
                $todosDossiers,
                $fichiersParDossier
            );
        }

        // 4. Ajouter les fichiers sans dossier
        $fichiersSansDossier = $fichiersParDossier->get(null);
        if ($fichiersSansDossier && $fichiersSansDossier->count() > 0) {
            $result['sans_dossier'] = [
                'type' => 'sans_dossier',
                'dossier' => null,
                'nom_dossier' => 'Fichiers sans dossier',
                'path' => null,
                'profondeur' => 0,
                'description' => 'Fichiers qui ne sont pas classés dans un dossier',
                'couleur' => '#6B7280',
                'icone' => 'folder',
                'fichiers' => FichierResource::collection($fichiersSansDossier),
                'sous_dossiers' => [],
                'count_fichiers' => $fichiersSansDossier->count(),
                'count_total' => $fichiersSansDossier->count(),
                'taille_totale' => $fichiersSansDossier->sum('taille'),
                'taille_formatee' => $this->formatBytes($fichiersSansDossier->sum('taille'))
            ];
        }

        return $result;
    }

    /**
     * Construire la hiérarchie d'un dossier avec ses fichiers et sous-dossiers
     */
    private function buildDossierHierarchyWithFiles($dossier, $todosDossiers, $fichiersParDossier): array
    {
        // Fichiers directs du dossier
        $fichiersDuDossier = $fichiersParDossier->get($dossier->id, collect());

        // Sous-dossiers
        $sousDossiers = $todosDossiers->where('parent_id', $dossier->id);
        $sousDossiersData = [];
        $totalFichiers = $fichiersDuDossier->count();
        $totalTaille = $fichiersDuDossier->sum('taille');

        foreach ($sousDossiers as $sousDossier) {
            $sousDossierData = $this->buildDossierHierarchyWithFiles(
                $sousDossier,
                $todosDossiers,
                $fichiersParDossier
            );
            $sousDossiersData[] = $sousDossierData;

            // Ajouter aux totaux
            $totalFichiers += $sousDossierData['count_total'];
            $totalTaille += $sousDossierData['taille_totale'];
        }

        // Charger les fichiers dans le dossier pour DossierResource
        $dossier->setRelation('fichiers', $fichiersDuDossier);

        return [
            'type' => 'dossier',
            'dossier' => new DossierResource($dossier),
            'nom_dossier' => $dossier->nom,
            'path' => $dossier->path,
            'profondeur' => $dossier->profondeur,
            'parent_id' => $dossier->parent_id,
            'couleur' => $dossier->couleur,
            'icone' => $dossier->icone,
            'description' => $dossier->description,

            // Fichiers directs
            'fichiers' => FichierResource::collection($fichiersDuDossier),
            'count_fichiers' => $fichiersDuDossier->count(),

            // Sous-dossiers
            'sous_dossiers' => $sousDossiersData,
            'count_sous_dossiers' => count($sousDossiersData),

            // Totaux (incluant sous-dossiers)
            'count_total' => $totalFichiers,
            'taille_totale' => $totalTaille,
            'taille_formatee' => $this->formatBytes($totalTaille),

            // Navigation
            'breadcrumb' => $dossier->getBreadcrumb(),
            'can_navigate_up' => $dossier->parent_id !== null
        ];
    }

    /**
     * Obtenir la structure hiérarchique des dossiers accessibles
     */
    private function getFolderStructure(User $user): array
    {
        // Récupérer tous les dossiers accessibles
        $dossiers = Dossier::query()
            ->with(['createdBy'])
            ->where(function($q) use ($user) {
                $q->where('is_public', true)
                  ->orWhere('created_by', $user->id);
            })
            ->orderBy('profondeur')
            ->orderBy('nom')
            ->get();

        return $this->buildFolderTreeWithResource($dossiers);
    }

    /**
     * Construire l'arbre hiérarchique avec DossierResource
     */
    private function buildFolderTreeWithResource($dossiers): array
    {
        $tree = [];
        $indexed = [];

        // Créer les resources et indexer
        foreach ($dossiers as $dossier) {
            $resource = new DossierResource($dossier);
            $indexed[$dossier->id] = [
                'resource' => $resource,
                'parent_id' => $dossier->parent_id,
                'enfants' => []
            ];
        }

        // Construire l'arbre
        foreach ($indexed as $id => $item) {
            if ($item['parent_id'] === null) {
                // Dossier racine
                $tree[] = array_merge($item['resource']->toArray(request()), [
                    'enfants' => &$indexed[$id]['enfants']
                ]);
            } else {
                // Dossier enfant
                if (isset($indexed[$item['parent_id']])) {
                    $indexed[$item['parent_id']]['enfants'][] = array_merge(
                        $item['resource']->toArray(request()),
                        ['enfants' => &$indexed[$id]['enfants']]
                    );
                }
            }
        }

        return $tree;
    }

    /**
     * Compter le nombre de dossiers actifs
     */
    private function countActiveFolders(User $user): int
    {
        return Dossier::query()
            ->where(function($q) use ($user) {
                $q->where('is_public', true)
                  ->orWhere('created_by', $user->id);
            })
            ->count();
    }

    /**
     * Formater les bytes en format lisible
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes == 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
