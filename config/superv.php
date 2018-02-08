<?php

use SuperV\Platform\Packs\Auth\PlatformUser;

return [
    'installed' => env('SUPERV_INSTALLED', false),
    'droplets' => [
        'location' => env('SUPERV_DROPLETS', 'droplets')
    ],
    'assets' => [
        'live' => true,
    ],
    'ports'  => [
        'web' => [
            'hostname' => env('SUPERV_HOSTNAME'),
            'theme'    => env('SUPERV_WEB_THEME'),
        ],
        'acp' => [
            'hostname' => env('SUPERV_HOSTNAME'),
            'prefix'   => 'acp',
            'theme'    => env('SUPERV_ACP_THEME'),
        ],
        'api' => [
            'hostname' => 'api.'.env('SUPERV_HOSTNAME'),
            'prefix'   => 'v1',
        ],
    ],
    'auth' => [
        'user' => [
            'model' => PlatformUser::class
        ]
    ],
    'clockwork' => env('SUPERV_CLOCKWORK', false)
];