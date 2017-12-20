<?php

namespace SuperV\Platform\Domains\Auth;

use Spatie\Permission\PermissionServiceProvider;
use SuperV\Platform\Contracts\ServiceProvider;
use Tymon\JWTAuth\Providers\JWTAuthServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
       $this->app->register(PermissionServiceProvider::class);
       $this->app->register(JWTAuthServiceProvider ::class);
    }
}
