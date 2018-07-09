<?php

namespace SuperV\Platform\Domains\Auth;

use Auth;
use SuperV\Platform\Domains\Auth\Console\CreateUserCommand;
use SuperV\Platform\Domains\Auth\Events\UserCreatedEvent;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Providers\BaseServiceProvider;

class AuthServiceProvider extends BaseServiceProvider
{
    protected $_bindings = [
        'SuperV\Platform\Domains\Auth\Contracts\Account' => Account::class
    ];

    protected $commands = [
        CreateUserCommand::class
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

        config()->set('auth.defaults.guard', 'platform');

        config()->set('auth.guards.platform', [
            'driver'   => 'session',
            'provider' => 'platform',
        ]);

        config()->set('auth.guards.superv-api', [
            'driver'   => 'jwt',
            'provider' => 'platform',
        ]);

        config()->set('auth.providers.platform', [
            'driver' => 'platform',
        ]);
    }
}