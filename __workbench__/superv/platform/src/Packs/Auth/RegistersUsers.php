<?php

namespace SuperV\Platform\Packs\Auth;

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

    public function register(UserRegistrar $registrar)
    {
        $registrar(request(['email', 'password', 'password_confirmation']));

        return redirect()->to($this->getRedirectTo());
    }

    public function getRedirectTo()
    {
        return $this->redirectTo;
    }
}