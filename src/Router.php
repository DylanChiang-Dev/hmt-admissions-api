<?php

namespace App;

use App\Middleware\Pipeline;

class Router
{
    private array $routes = [];
    private Pipeline $pipeline;

    public function __construct()
    {
        $this->pipeline = new Pipeline();
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->pipeline->pipe($middleware);
    }

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        // Simple exact match routing for now
        $matchedHandler = null;
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $matchedHandler = $route['handler'];
                break;
            }
        }

        if (!$matchedHandler) {
            // Handle 404 through the pipeline as well, effectively
            // Or just return 404 directly from the core handler if no route matches
            $coreHandler = function (Request $req) {
                 return Response::error('ROUTE_NOT_FOUND', 'Route not found', null, 404);
            };
        } else {
            $coreHandler = function (Request $req) use ($matchedHandler) {
                // Determine if handler returns Response object or data
                $result = $matchedHandler($req);
                if ($result instanceof Response) {
                    return $result;
                }
                return Response::json($result);
            };
        }

        $response = $this->pipeline->process($request, $coreHandler);
        $response->send();
    }
}
