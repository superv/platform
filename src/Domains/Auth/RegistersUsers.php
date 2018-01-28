<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Foundation\Validation\ValidatesRequests;

trait RegistersUsers
{
    use ValidatesRequests;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    public function register()
    {
        $this->validate(request(), [
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'email'    => request('email'),
            'password' => bcrypt(request('password')),
        ]);

        event(new UserCreatedEvent($user, request()));

        return redirect()->to($this->getRedirectTo());
    }

    public function getRedirectTo()
    {
        return $this->redirectTo;
    }
}