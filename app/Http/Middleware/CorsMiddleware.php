<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /* $allowed_origins = [
            'http://192.168.8.106:3000', // ou ton URL frontend (Vite)
            'http://localhost:3000'
        ];

        $origin = $request->headers->get('Origin');

        Log::info('Origin reçu : '.$origin); */

        $headers = [
            'Access-Control-Allow-Origin'       => '*',

            'Access-Control-Allow-Methods'      => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials'  => 'true',
            'Access-Control-Max-Age'            => '86400',
            'Access-Control-Allow-Headers'      => 'Content-Type, Authorization, X-Requested-With',
            "Access-Control-Expose-Headers"     => "Content-Disposition, Content-Type, Content-Length", // <-- très important
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;

        /*return $next($request)
        ->header->('Access-Control-Allow-Origin', '*')
        ->header->('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header->('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization');*/
    }
}
