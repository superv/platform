<?php

return [
    'sections' => [
        [
            'title' => 'Users',
            'slug'  => 'users',
            'icon' => 'users',
            'priority' => 5
        ],
    ],

    'pages' => [
        [
            'title'   => 'New User',
            'icon'    => 'plus',
            'url'     => 'auth/users/create',
            'section' => 'users',
            'priority' => 5

        ],
        [
            'title'   => 'List Users',
            'icon'    => 'list',
            'url'     => 'auth/users',
            'section' => 'users',
            'priority' => 10

        ],

    ],
];
