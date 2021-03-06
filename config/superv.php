<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SuperV Platform Status
    |--------------------------------------------------------------------------
    |
    |
    */

    'installed' => env('SV_INSTALLED', false),

    /*
    |--------------------------------------------------------------------------
    | Base Application Hostname
    |--------------------------------------------------------------------------
    |
    |
    */

    'hostname' => env('SV_HOSTNAME', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Addons Location
    |--------------------------------------------------------------------------
    |
    |
    */

    'addons' => [
        'location' => env('SV_ADDONS', 'addons'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets Configuration
    |--------------------------------------------------------------------------
    |
    |
    */

    'assets' => [
        'live' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth Configuration
    |--------------------------------------------------------------------------
    |
    |
    */

    'auth' => [
        'user' => [
            'model' => 'SuperV\Platform\Domains\Auth\User',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Port Configuration
    |--------------------------------------------------------------------------
    |
    |
    */

    'ports' => [
        'default' => 'SuperV\Platform\Domains\Port\ApiPort',
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Tools Configuration
    |--------------------------------------------------------------------------
    |
    |
    */

    'clockwork' => env('SV_CLOCKWORK', false),
];