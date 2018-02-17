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

    public function register(UserRegistrar $registrar)
    {
        $registrar->setRequest(request()->all())->register();

        return redirect()->to($this->getRedirectTo());
    }

    public function getRedirectTo()
    {
        return $this->redirectTo;
    }
}