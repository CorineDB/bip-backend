<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Traits\CachableService;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Contracts\RoleServiceInterface;
use App\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class RoleService extends BaseService implements RoleServiceInterface
{
    use CachableService;

    // Configuration du cache
    protected int $cacheTtl = 3600; // 1h - dépend des permissions utilisateur
    protected array $cacheTags = ['roles'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return RoleResource::class;
    }

    /**
     * Tous les rôles avec cache par utilisateur
     */
    public function all(): JsonResponse
    {
        try {


            $user = Auth::user();
            $params = [
                'user_id' => $user->id,
                'profilable_id' => $user->profilable_id,
                'profilable_type' => $user->profilable_type
            ];

            $query = $this->repository->getModel()->whereNotIn('slug', ['super-admin', 'dpaf', 'dgpd', 'organisation'])
                ->where("roleable_id", $user->profilable_id)
                ->where("roleable_type", $user->profilable_type);

            // Si l'utilisateur est super admin (profilable_type et profilable_id sont null),
            // exclure les rôles spécifiés
            if (is_null($user->profilable_type) && is_null($user->profilable_id)) {
                $query->whereNotIn('slug', ['analyste-dgpd']);
            }

            $items = $query->get();

            return $this->resourceClass::collection($items->load('permissions'))->response();
            if ($this->cacheExists('all', $params)) {
                $cached = $this->cacheGet('all', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $query = $this->repository->getModel()->whereNotIn('slug', ['super-admin', 'dpaf', 'dgpd', 'organisation'])
                ->where("roleable_id", $user->profilable_id)
                ->where("roleable_type", $user->profilable_type);

            // Si l'utilisateur est super admin (profilable_type et profilable_id sont null),
            // exclure les rôles spécifiés
            if (is_null($user->profilable_type) && is_null($user->profilable_id)) {
                $query->whereNotIn('slug', ['analyste-dgpd']);
            }

            $items = $query->get();

            $responseData = [];
            foreach ($items->load('permissions') as $item) {
                $responseData[] = (new RoleResource($item))->resolve();
            }

            $this->cachePut('all', $responseData, $params);

            return response()->json($responseData);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Un rôle par ID avec cache
     */
    /*public function find(int|string $id): JsonResponse
    {
        try {
            $params = ['id' => $id];

            if ($this->cacheExists('find', $params)) {
                $cached = $this->cacheGet('find', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $item = $this->repository->findOrFail($id);
            $responseData = (new RoleResource($item))->resolve();
            $this->cachePut('find', $responseData, $params);

            return response()->json($responseData);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Permissions d'un rôle avec cache
     */
    public function permissions(int|string $id): JsonResponse{
        try {


            $item = $this->repository->findOrFail($id)->load('permissions');

            return $this->resourceClass::collection($item)->response();

            $item = $this->repository->findOrFail($id)->load('permissions');
            $params = ['role_id' => $id, 'with_permissions' => true];

            if ($this->cacheExists('permissions', $params)) {
                $cached = $this->cacheGet('permissions', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $item = $this->repository->findOrFail($id);
            $responseData = (new RoleResource($item->load('permissions')))->resolve();
            $this->cachePut('permissions', $responseData, $params);

            return response()->json($responseData);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Create a new role with permissions.
     *
     * @param array $data
     * @return JsonResponse
     */
    public function create(array $data): JsonResponse
    {
        try {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $data['roleable_id'] = Auth::user()->profilable_id;
            $data['roleable_type'] = Auth::user()->profilable_type;

            $item = $this->repository->create($data);

            if (!empty($permissions)) {
                $item->permissions()->sync($permissions);
            }

            // Invalider le cache de l'utilisateur courant
            $user = Auth::user();
            $params = [
                'user_id' => $user->id,
                'profilable_id' => $user->profilable_id,
                'profilable_type' => $user->profilable_type
            ];
            $this->cacheForget('all', $params);

            return (new $this->resourceClass($item->load('permissions')))
                ->additional(['message' => 'Role created successfully.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update a role with permissions.
     *
     * @param int|string $id
     * @param array $data
     * @return JsonResponse
     */
    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $updated = $this->repository->update($id, $data);
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found or not updated.',
                ], 404);
            }

            $item = $this->repository->findOrFail($id);

            if (!empty($permissions)) {
                $item->permissions()->sync($permissions);
            }

            // Invalider les caches liés à ce rôle
            $this->cacheForget('find', ['id' => $id]);
            $this->cacheForget('permissions', ['role_id' => $id, 'with_permissions' => true]);

            // Invalider le cache de l'utilisateur courant
            $user = Auth::user();
            $params = [
                'user_id' => $user->id,
                'profilable_id' => $user->profilable_id,
                'profilable_type' => $user->profilable_type
            ];
            $this->cacheForget('all', $params);

            return (new $this->resourceClass($item->load('permissions')))
                ->additional(['message' => 'Role updated successfully.'])
                ->response();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
