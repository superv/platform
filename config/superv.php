<?php

use SuperV\Platform\Domains\Auth\Client;
use SuperV\Platform\Domains\Auth\User;

return [
    'installed' => env('SV_INSTALLED', false),
    'droplets'  => [
        'location' => env('SV_DROPLETS', 'droplets'),
    ],
    'assets'    => [
        'live' => true,
    ],
    'ports'     => [
        'web' => [
            'hostname'           => env('SV_HOSTNAME'),
            'theme'              => env('SV_WEB_THEME'),
            'allowed_user_types' => ['client'],
            'model'              => Client::class,
        ],
        'acp' => [
            'hostname' => env('SV_HOSTNAME'),
            'prefix'   => 'acp',
            'theme'    => env('SV_ACP_THEME'),
        ],
        'api' => [
            'hostname' => 'api.'.env('SV_HOSTNAME'),
            'prefix'   => 'v1',
        ],
    ],
    'auth'      => [
        'user' => [
            'model' => User::class,
        ],
    ],
    'twig'      => [
        'enabled' => true,
    ],
    'clockwork' => env('SV_CLOCKWORK', false),
];