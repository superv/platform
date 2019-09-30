<?php

namespace SuperV\Platform\Domains\Auth;

use Auth;
use SuperV\Platform\Domains\Auth\Console\AssignRoleCommand;
use SuperV\Platform\Domains\Auth\Console\SuperVUserCommand;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Http\Middleware\PlatformAuthenticate;
use SuperV\Platform\Providers\BaseServiceProvider;
use Tymon\JWTAuth\Providers\LaravelServiceProvider as JwtAuthServiceProvider;

class AuthServiceProvider extends BaseServiceProvider
{
    protected $commands = [
        SuperVUserCommand::class,
        AssignRoleCommand::class,
    ];

    public function register()
    {
        parent::register();

        $this->app->register(JwtAuthServiceProvider::class);

        $this->registerListeners([
            PortDetectedEvent::class => function (PortDetectedEvent $event) {
                if ($guard = $event->port->guard()) {
                    config()->set('auth.defaults.guard', $guard);
                }
            },
        ]);
    }

    public function boot()
    {
        $this->registerUserProvider();

        $this->aliasMiddleware();

        $this->registerAuthGuard();

//        Collection::macro('guard', function () {
//            $this->items = sv_guard($this->items);
//
//            return $this;
//        });
    }

    /**
     * Alias the middleware.
     *
     * @return void
     */
    protected function aliasMiddleware()
    {
        $router = $this->app['router'];

        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';

        $router->$method('sv.auth', PlatformAuthenticate::class);
    }

    protected function registerAuthGuard()
    {
        config()->set('auth.guards.sv-api', [
            'driver'   => 'superv-jwt',
            'provider' => 'platform',
        ]);

        /**
         * Extend default JWT guard for port-base token authentication
         */
        $this->app['auth']->extend('superv-jwt', function ($app, $name, array $config) {
            $guard = new JWTGuard(
                $app['tymon.jwt'],
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    protected function registerUserProvider(): void
    {
        Auth::provider('platform', function ($app) {
            return new PlatformUserProvider($app['hash'], config('superv.auth.user.model'));
        });

        config()->set('auth.providers.platform', [
            'driver' => 'platform',
            'model'  => config('superv.auth.user.model'),
        ]);
    }
}