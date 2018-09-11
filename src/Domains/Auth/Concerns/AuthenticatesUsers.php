<?php

namespace SuperV\Platform\Domains\Auth\Concerns;

use Illuminate\Http\Request;

trait AuthenticatesUsers
{
    public function login(Request $request)
    {
        $guard = auth()->guard('platform');
        if (! $guard->attempt($request->only(['email', 'password']))) {
            return redirect()->back()
                             ->withInput(request(['email']))
                             ->withErrors([
                                 'email' => 'Invalid credentials',
                             ]);
        }

        return redirect()->to($this->redirectTo());
    }

    public function redirectTo()
    {
        return $this->redirectTo ?? '/';
    }
}