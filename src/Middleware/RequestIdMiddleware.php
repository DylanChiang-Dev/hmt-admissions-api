<?php

namespace App\Middleware;

use App\Request;
use App\Response;
use App\Utils\Uuid;

class RequestIdMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        $requestId = $request->getHeader('X-Request-Id');

        if (empty($requestId)) {
            $requestId = Uuid::generate();
        }

        $request->setAttribute('request_id', $requestId);

        /** @var Response $response */
        $response = $next($request);

        $response->setHeader('X-Request-Id', $requestId);

        return $response;
    }
}
