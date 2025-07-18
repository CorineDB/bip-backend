<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TrackInfos\StoreTrackInfoRequest;
use App\Http\Requests\TrackInfos\UpdateTrackInfoRequest;
use App\Services\Contracts\TrackInfoServiceInterface;
use Illuminate\Http\JsonResponse;

class TrackInfoController extends Controller
{
    protected TrackInfoServiceInterface $service;

    public function __construct(TrackInfoServiceInterface $service)
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

    public function store(StoreTrackInfoRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateTrackInfoRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}