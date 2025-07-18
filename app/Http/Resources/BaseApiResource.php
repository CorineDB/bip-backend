<?php

namespace App\Http\Resources;

use App\Http\Resources\Contracts\ApiResourceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseApiResource extends JsonResource implements ApiResourceInterface
{

    /**
     * The resource instance.
     *
     * @var mixed
     */
    public $resource;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->resource = $resource;
    }

    /**
     * Transform the resource into a JSON array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "nom"=> $this->nom
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
            'resource_info' => [
                'type' => 'single',
                'id' => $this->resource->id ?? null,
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
    }

    /**
     * Create a new resource instance for a collection.
     * Note: Consider using BaseCollectionResource for better collection handling.
     *
     * @param mixed $resource
     * @return static
     */
    public static function collection($resource)
    {
        return parent::collection($resource)->additional([
            'success' => true,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Transform the resource into a wrapped response.
     *
     * @param Request $request
     * @return array
     */
    protected function wrapData(Request $request): array
    {
        return [
            'data' => $this->toArray($request),
        ];
    }
}