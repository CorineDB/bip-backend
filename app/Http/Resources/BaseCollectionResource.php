<?php

namespace App\Http\Resources;

use App\Http\Resources\Contracts\ApiResourceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseCollectionResource extends ResourceCollection implements ApiResourceInterface
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => $this->getMeta($request),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'timestamp' => now()->toISOString(),
            'collection_info' => [
                'count' => $this->collection->count(),
                'type' => 'collection',
            ],
        ];
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
        $response->header('X-Collection-Count', $this->collection->count());
    }

    /**
     * Get meta information for the collection.
     *
     * @param Request $request
     * @return array
     */
    protected function getMeta(Request $request): array
    {
        return [
            'total' => $this->collection->count(),
            'per_page' => $request->get('per_page', 15),
            'current_page' => $request->get('page', 1),
            'next_page' => $request->get('page', 1),
            'last_page' => $request->get('page', 1),
        ];
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
}