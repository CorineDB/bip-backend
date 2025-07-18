<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Contracts\DepartementServiceInterface;
use Illuminate\Http\JsonResponse;

class DepartementController extends Controller
{
    protected DepartementServiceInterface $service;

    public function __construct(DepartementServiceInterface $service)
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