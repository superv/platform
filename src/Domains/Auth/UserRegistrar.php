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
            'type'     => 'required',
        ])->validate();

        $user = User::create([
            'email'    => $request['email'],
            'password' => bcrypt($request['password']),
            'type'     => $request['type'],
        ]);

        event(new UserCreatedEvent($user, $request));

        return $user;
    }
}