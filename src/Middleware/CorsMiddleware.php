<?php

namespace App\Middleware;

use App\Request;
use App\Response;

class CorsMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        // Handle Preflight Options
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Request-Id',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        /** @var Response $response */
        $response = $next($request);

        // Add CORS headers to ALL responses
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Request-Id');

        return $response;
    }
}
