<?php

namespace SuperV\Platform\Http\Controllers;

class AuthController
{
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = $this->guard()->attempt($credentials, true)) {
            return response()->json(['status' => 'error', 'error' => ['description' => 'Invalid credentials']], 401);
        }

        return response()->json([
            'status' => 'ok',
            'data'   => [
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => $this->guard()->factory()->getTTL() * 60,
            ],
        ]);
    }

    protected function guard()
    {
        return auth()->guard('sv-api');
    }
}
