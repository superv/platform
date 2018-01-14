<?php

namespace SuperV\Platform\Domains\Auth\Handlers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SuperV\Modules\Ui\Domains\Form\FormHandler;
use SuperV\Modules\Ui\Exceptions\FormHandlerException;

class LoginFormHandler extends FormHandler
{
    use AuthenticatesUsers;

    public function handle(Request $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            throw new AuthenticationException("Too many attempts");
        }

        if ($this->guard()->attempt(
            $this->post->only('email', 'password')->toArray(), $request->filled('remember')
        )) {
            return ['redirect' => url('/')];
        }
        $this->incrementLoginAttempts($request);

        throw new FormHandlerException("Invalid credentials");
    }

    protected function guard()
    {
        return Auth::guard('acp');
    }
}