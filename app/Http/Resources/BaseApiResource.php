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
        return $this->formatResourceData();
    }

    /**
     * Format resource data dynamically based on fillable attributes.
     *
     * @return array<string, mixed>
     */
    protected function formatResourceData(): array
    {
        $data = [];

        // Get fillable attributes from the model
        if (method_exists($this->resource, 'getFillable')) {
            $fillable = $this->resource->getFillable();

            foreach ($fillable as $attribute) {
                if (isset($this->resource->{$attribute})) {
                    $value = $this->resource->{$attribute};
                    $data[$attribute] = $this->formatAttribute($attribute, $value);
                }
            }
        }

        // Always include id if it exists
        if (isset($this->resource->id)) {
            $data['id'] = $this->resource->id;
        }

        // Include timestamps if they exist
        foreach (['created_at', 'updated_at', 'deleted_at'] as $timestamp) {
            if (isset($this->resource->{$timestamp})) {
                $data[$timestamp] = $this->formatDate($this->resource->{$timestamp});
            }
        }

        return $data;
    }

    /**
     * Format a specific attribute value.
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     */
    protected function formatAttribute(string $attribute, $value)
    {
        // Check if the attribute is a date field
        if ($this->isDateAttribute($attribute) && $value !== null) {
            return $this->formatDate($value);
        }

        return $value;
    }

    /**
     * Check if an attribute is a date field.
     *
     * @param string $attribute
     * @return bool
     */
    protected function isDateAttribute(string $attribute): bool
    {
        $dateFields = [
            'created_at', 'updated_at', 'deleted_at', 'date_',
            '_date', '_at', 'date_debut', 'date_fin', 'date_soumission',
            'date_validation', 'date_approbation', 'date_creation'
        ];

        foreach ($dateFields as $dateField) {
            if (str_contains($attribute, $dateField)) {
                return true;
            }
        }

        // Check if the model has date casts
        if (method_exists($this->resource, 'getDates')) {
            return in_array($attribute, $this->resource->getDates());
        }

        // Check if the model has casts defined for this attribute as date/datetime
        if (method_exists($this->resource, 'getCasts')) {
            $casts = $this->resource->getCasts();
            if (isset($casts[$attribute])) {
                return in_array($casts[$attribute], ['date', 'datetime', 'timestamp']);
            }
        }

        return false;
    }

    /**
     * Format date value.
     *
     * @param mixed $date
     * @return string|null
     */
    protected function formatDate($date): ?string
    {
        if ($date === null) {
            return null;
        }

        try {
            if (is_string($date)) {
                $date = \Carbon\Carbon::parse($date);
            }

            if ($date instanceof \Carbon\Carbon) {
                return $date->format("Y-m-d");
            }

            if ($date instanceof \DateTime) {
                return \Carbon\Carbon::instance($date)->format("Y-m-d");
            }

            return (string) $date;
        } catch (\Exception $e) {
            return (string) $date;
        }
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
                'id' => $this->resource->hashed_id ?? null,
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
