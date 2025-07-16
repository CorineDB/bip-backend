<?php

namespace App\Http\Resources\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ApiResourceInterface
{
    /**
     * Create a new resource instance.
     *
     * @param  mixed  ...$parameters
     * @return static
     */
    public static function make(...$parameters);

    /**
     * Create a new resource collection.
     *
     * @param  mixed  $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collection($resource);

    /**
     * Transform the resource into a JSON array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request);

    /**
     * Resolve the resource to an array.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array
     */
    public function resolve($request = null);

    /**
     * Convert the resource to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request);

    /**
     * Customize the response for a request.
     *
     * @param Request $request
     * @param JsonResponse $response
     * @return void
     */
    public function withResponse(Request $request, $response);

    /**
     * Add additional meta data to the resource response.
     *
     * @param  array  $data
     * @return static
     */
    public function additional(array $data);

    /**
     * Get the JSON serialization options.
     *
     * @return int
     */
    public function jsonOptions();

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return JsonResponse
     */
    public function response($request = null);

    /**
     * Set the string that is used to wrap the outer-most resource array.
     *
     * @param  string  $value
     * @return void
     */
    public static function wrap($value);

    /**
     * Disable the wrapping of the outer-most resource array.
     *
     * @return void
     */
    public static function withoutWrapping();
}