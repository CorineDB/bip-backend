<?php

namespace App\Http\Resources;

use App\Http\Resources\Contracts\ApiResourceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseExternalResource extends JsonResource implements ApiResourceInterface
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return [];
    }

    /**
     * Customize the response for a request.
     *
     * @param Request $request
     * @param \Illuminate\Http\JsonResponse $response
     * @return void
     */
    public function withResponse(Request $request, $response): void
    {
        $response->header('Content-Type', 'application/json');
    }

    /**
     * Create a new resource instance for a collection.
     *
     * @param mixed $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collection($resource)
    {
        return parent::collection($resource);
    }

    /**
     * Transform the resource into a minimal array for external use.
     *
     * @param Request $request
     * @return array
     */
    public function toExternalArray(Request $request): array
    {
        return $this->toArray($request);
    }

    /**
     * Get the JSON serialization options.
     *
     * @return int
     */
    public function jsonOptions(): int
    {
        return JSON_PRESERVE_ZERO_FRACTION;
    }

    /**
     * Resolve the resource to an array for external consumption.
     *
     * @param Request|null $request
     * @return array
     */
    public function resolveExternal($request = null): array
    {
        $request = $request ?? request();
        return $this->toExternalArray($request);
    }
}