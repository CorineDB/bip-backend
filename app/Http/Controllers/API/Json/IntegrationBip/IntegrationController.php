<?php

namespace App\Http\Controllers\API\Json\IntegrationBip;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntegrationBip\UpdateProjetStatusRequest;
use App\Services\Contracts\IntegrationBipServiceInterface;
use Illuminate\Http\JsonResponse;

class IntegrationController extends Controller
{
    protected IntegrationBipServiceInterface $service;

    public function __construct(IntegrationBipServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->service->getProjetsArrivesAMaturite();
    }

    public function show(int $idProjet): JsonResponse
    {
        return $this->service->getProjet($idProjet);
    }

    public function update(UpdateProjetStatusRequest $request, int $idProjet): JsonResponse
    {
        return $this->service->updateProjetStatus($idProjet, $request->validated());
    }
}
