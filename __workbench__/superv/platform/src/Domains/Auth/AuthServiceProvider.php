<?php

namespace SuperV\Platform\Domains\Auth;

use SuperV\Platform\Domains\Auth\Events\UserCreatedEvent;
use SuperV\Platform\Providers\BaseServiceProvider;

class AuthServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->registerListeners([
            UserCreatedEvent::class => function (UserCreatedEvent $event) {
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
        ]);
    }
}