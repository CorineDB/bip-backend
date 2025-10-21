<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\financements\StoreFinancementRequest;
use App\Http\Requests\financements\UpdateFinancementRequest;
use App\Services\Contracts\FinancementServiceInterface;
use Illuminate\Http\JsonResponse;

class FinancementController extends Controller
{
    protected FinancementServiceInterface $service;

    public function __construct(FinancementServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function store(StoreFinancementRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateFinancementRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    public function types_de_financement(): JsonResponse
    {
        return $this->service->types_de_financement();
    }

    public function natures_type_de_financement($idType): JsonResponse
    {
        return $this->service->natures_type_de_financement($idType);
    }

    public function natures_de_financement(): JsonResponse
    {
        return $this->service->natures_de_financement();
    }

    public function sources_nature_de_financement($idNature): JsonResponse
    {
        return $this->service->sources_nature_de_financement($idNature);
    }

    public function sources_de_financement(): JsonResponse
    {
        return $this->service->sources_de_financement();
    }

    /**
     * Charger les financements avec filtres et dépendances hiérarchiques
     */
    public function financementsWithFilters(Request $request): JsonResponse
    {
        $filterType = $request->get('filter_type');
        $parentId = $request->get('parent_id');
        $dependsOn = $request->get('depends_on');

        switch ($filterType) {
            case 'type':
                return $this->service->types_de_financement();

            case 'nature':
                // Si on a un parent_id (types_financement sélectionné), filtrer par type
                if ($parentId && is_numeric($parentId)) {
                    return $this->service->natures_type_de_financement($parentId);
                }
                return $this->service->natures_de_financement();

            case 'source':
                // Si on a un parent_id (natures_financement sélectionné), filtrer par nature
                if ($parentId && is_numeric($parentId)) {
                    return $this->service->sources_nature_de_financement($parentId);
                }
                return $this->service->sources_de_financement();

            default:
                return $this->service->all();
        }
    }
}