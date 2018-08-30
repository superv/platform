<?php

use SuperV\Platform\Domains\Navigation\Section;

return [
    'acp_main' => [
        [
            'title' => 'Dashboard',
            'icon'  => 'tachometer',
            'url'   => 'platform/dashboard',
        ],
        Section::make('platform')
               ->icon('cog')
               ->url('platform')
               ->sections([
                   Section::make('user_management')
                          ->sections([
                              Section::make('users')->url('platform/users'),
                              Section::make('roles')->url('platform/roles'),
                          ]),
                   [
                       'title'    => 'Settings',
                       'icon'     => 'cog',
                       'sections' => [
                           Section::make('localization')->url('platform/localization'),
                           ['slug' => 'droplets', 'url' => 'platform/droplets'],
                       ],
                   ],
               ]),
    ],
];