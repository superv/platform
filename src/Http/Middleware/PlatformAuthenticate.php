<?php

namespace SuperV\Platform\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;

class PlatformAuthenticate extends Authenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return redirect()->to('/');
        }
    }

    public function guard($request, $guard)
    {
        if ($this->auth->guard($guard)->check()) {
            return $this->auth->shouldUse($guard);
        }

        throw new AuthenticationException(
            'Unauthenticated.', [$guard], $this->redirectTo($request)
        );
    }
}
