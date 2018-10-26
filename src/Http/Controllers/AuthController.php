<?php

namespace SuperV\Platform\Http\Controllers;

class AuthController
{
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials, true)) {
            return response()->json(['status' => 'error', 'error' => ['description' => 'Invalid Credentials']]);
        }

        return response()->json([
            'status' => 'ok',
            'data'   => [
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth()->guard()->factory()->getTTL() * 60,
            ],
        ]);
    }
}