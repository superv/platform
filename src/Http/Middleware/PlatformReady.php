<?php

namespace SuperV\Platform\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SuperV\Platform\Contracts\Dispatcher;

class PlatformReady
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $this->dispatcher->dispatch('superv.app.loaded');

        if ($response instanceof Response) {
            return $response;
        }

        return $next($request);
    }
}
