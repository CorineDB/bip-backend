<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contracts\PermissionServiceInterface;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    protected PermissionServiceInterface $service;

    public function __construct(PermissionServiceInterface $service)
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