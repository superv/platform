<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Validation\Factory;
use SuperV\Platform\Domains\Auth\Events\UserCreatedEvent;

class UserRegistrar
{
    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke(array $request)
    {
        $this->factory->make($request, [
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ])->validate();

        $user = PlatformUser::create([
            'email'    => request('email'),
            'password' => bcrypt(request('password')),
        ]);

        event(new UserCreatedEvent($user));

        return $user;
    }
}