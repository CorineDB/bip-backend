<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Contracts\CommuneServiceInterface;
use Illuminate\Http\JsonResponse;

class CommuneController extends Controller
{
    protected CommuneServiceInterface $service;

    public function __construct(CommuneServiceInterface $service)
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