<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\EvaluationCriteres\StoreEvaluationCritereRequest;
use App\Http\Requests\EvaluationCriteres\UpdateEvaluationCritereRequest;
use App\Services\Contracts\EvaluationCritereServiceInterface;
use Illuminate\Http\JsonResponse;

class EvaluationCritereController extends Controller
{
    protected EvaluationCritereServiceInterface $service;

    public function __construct(EvaluationCritereServiceInterface $service)
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

    public function store(StoreEvaluationCritereRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateEvaluationCritereRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}