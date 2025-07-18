<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Decisions\StoreDecisionRequest;
use App\Http\Requests\Decisions\UpdateDecisionRequest;
use App\Services\Contracts\DecisionServiceInterface;
use Illuminate\Http\JsonResponse;

class DecisionController extends Controller
{
    protected DecisionServiceInterface $service;

    public function __construct(DecisionServiceInterface $service)
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

    public function store(StoreDecisionRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateDecisionRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}