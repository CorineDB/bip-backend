<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Contracts\ArrondissementServiceInterface;
use Illuminate\Http\JsonResponse;

class ArrondissementController extends Controller
{
    protected ArrondissementServiceInterface $service;

    public function __construct(ArrondissementServiceInterface $service)
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
}