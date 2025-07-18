<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Evaluations\StoreEvaluationRequest;
use App\Http\Requests\Evaluations\UpdateEvaluationRequest;
use App\Services\Contracts\EvaluationServiceInterface;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    protected EvaluationServiceInterface $service;

    public function __construct(EvaluationServiceInterface $service)
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

    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateEvaluationRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}