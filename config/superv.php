<?php

return [
    'installed' => env('SUPERV_INSTALLED', false),
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
            'hostname' => env('SUPERV_HOSTNAME'),
            'prefix'   => 'api',
        ],
    ],
    'clockwork' => env('SUPERV_CLOCKWORK', false)
];