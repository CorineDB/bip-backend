<?php

namespace App\Services\Traits;

trait ResponseJsonTrait{


	/**
     * Success response method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($message, $result = [], $code = 200)
    {
    	$response = [
            'success'       => true,
            'message'       => $message,
            'data'          => $result,
            'statutCode'    => $code
        ];

        return response()->json($response, $code);
    }

    /**
     * Error response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function errorResponse($message, $result = [], $code = 500)
    {
    	$response = [
            'success'       => false,
            'message'       => $message,
            'errors'        => $result,
            'statutCode'    => $code
        ];

        return response()->json($response, $code);
    }
}