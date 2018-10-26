<?php

namespace SuperV\Platform\Domains\Auth;

use Auth;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Auth\Console\AssignRoleCommand;
use SuperV\Platform\Domains\Auth\Console\CreateUserCommand;
use SuperV\Platform\Domains\Auth\Events\UserCreatedEvent;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Providers\BaseServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AuthServiceProvider extends BaseServiceProvider
{
    protected $commands = [
        CreateUserCommand::class,
        AssignRoleCommand::class
    ];

    public function register()
    {
        parent::register();

        $this->registerListeners([
            UserCreatedEvent::class  => function (UserCreatedEvent $event) {
                $user = $event->user;
                $request = $event->request;

                if (! $profile = array_get($request, 'profile')) {
                    return;
                }
                $user->createProfile([
                    'first_name' => $profile['first_name'],
                    'last_name'  => $profile['last_name'],
                ]);
            },
            PortDetectedEvent::class => function (PortDetectedEvent $event) {
                if ($model = $event->port->model()) {
                    config()->set('superv.auth.user.model', $model);
                }

                if ($guard = $event->port->guard()) {
                    config()->set('auth.defaults.guard', $guard);
                }
            },
        ]);
    }

    public function boot()
    {
        Auth::provider('platform', function ($app) {
            return new PlatformUserProvider($app['hash'], config('superv.auth.user.model'));
        });

        config()->set('auth.providers.platform', [
            'driver' => 'platform',
            'model'  => config('superv.auth.user.model'),
        ]);

        config()->set('auth.defaults.guard', 'platform');

        config()->set('auth.guards.platform', [
            'driver'   => 'session',
            'provider' => 'platform',
        ]);

        config()->set('auth.guards.superv-api', [
            'driver'   => 'superv-jwt',
            'provider' => 'platform',
        ]);

        $this->extendAuthGuard();

        Collection::macro('guard', function () {
            $this->items = sv_guard($this->items);

            return $this;
        });

        Relation::morphMap([
           'SuperV\Platform\Domains\Auth\User'
        ]);
    }

    protected function extendAuthGuard()
    {
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
}