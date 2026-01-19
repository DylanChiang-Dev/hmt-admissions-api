<?php

namespace App\Middleware;

use App\Request;
use App\Response;

class Pipeline
{
    private array $middleware = [];

    public function pipe(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function process(Request $request, callable $target): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function ($next, $middleware) {
                return function (Request $request) use ($next, $middleware) {
                    // Middleware signature: function(Request $request, callable $next): Response
                    return $middleware($request, $next);
                };
            },
            $target
        );

        return $pipeline($request);
    }
}
