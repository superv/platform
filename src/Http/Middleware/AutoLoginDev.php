<?php

namespace SuperV\Platform\Http\Middleware;

use Closure;
use Current;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Auth\Users;

class AutoLoginDev
{
    /**
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldLoginAutomatically($request)) {
            if ($userId = app(Users::class)->withEmail($this->getUser($request))->id) {
                auth()->onceUsingId($userId);
            }
        }

        return $next($request);
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    protected function getUser($request)
    {
        return $request->get('user', env('SV_TEST_USER', 'root@superv.io'));
    }

    protected function shouldLoginAutomatically(Request $request)
    {
        return Current::envIsLocal()
            && ! $request->hasHeader('authorization')
            && ! $request->has('token')
            && Current::port()->guard() === 'sv-api';
    }
}