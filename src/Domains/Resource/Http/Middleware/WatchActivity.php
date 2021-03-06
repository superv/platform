<?php

namespace SuperV\Platform\Domains\Resource\Http\Middleware;

use Closure;
use Current;
use SuperV\Platform\Domains\Resource\Resource\ResourceActivityEvent;

class WatchActivity
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! Current::port()) {
            return $next($request);
        }

        $uri = ltrim(Current::requestPath(), '/');

        if (starts_with($uri, 'sv/res/') || starts_with($uri, 'sv/ent/')) {
            ResourceActivityEvent::dispatch($request);
        }

        return $next($request);
    }
}