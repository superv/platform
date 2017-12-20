<?php

use SuperV\Platform\Domains\Auth\Domains\User\UserModel;

return [
    [
        'title'   => 'List Users',
        'route'   => [
            'as'   => 'auth::users.index',
            'port' => 'acp',
            'url'  => 'auth/users',
        ],
        'buttons' => [
            'auth::users.create',
        ],
        'handler' => [
            'type'   => 'block',
            'config' => [
                'component' => 'table',
                'model'     => UserModel::class,
                'buttons'   => [
                    'delete',
                    'edit' => ['href' => 'auth/users/{entry.id}/manage'],
                ],
                'query'     => [
                ],
            ],
        ],

    ],
    [
        'title'   => 'Create User',
        'route'   => [
            'as'   => 'auth::users.create',
            'port' => 'acp',
            'url'  => 'auth/users/create',
        ],
        'buttons' => [
            'auth::users.index',
            'auth::users.create',
        ],
        'handler' => [
            'type'   => 'block',
            'config' => [
                'component' => 'form',
                'form'      => 'user.json',
                'mode'      => 'create',
            ],
        ],
    ],
    [
        'title'   => 'Manage User',
        'route'   => [
            'as'   => 'auth::users.manage',
            'port' => 'acp',
            'url'  => 'auth/users/{id}/manage',
        ],
        'buttons' => [
            'auth::users.index',
            'auth::users.create',
        ],
        'handler' => [
            'type'   => 'block',
            'config' => [
                'component' => 'tabs',
                'tabs'      => [
                    'details'  => [
                        'title'   => 'Details',
                        'default' => true,
                        'route'   => 'auth::users.details',
                    ],
//                    'products' => [
//                        'title' => 'Products',
//                        'route' => 'auth::users.products',
//                    ],
                ],
            ],
        ],
    ],
    [
        'title'   => 'User Details',
        'route'   =>
            [
                'as'   => 'auth::users.details',
                'port' => 'acp',
                'url'  => 'auth/users/{id}/details',
            ],
        'handler' => [
            'type'   => 'block',
            'config' => [
                'component' => 'form',
                'form'      => 'user.json',
                'mode'      => 'edit',
            ],
        ],
    ],
    [
        'title'   => 'User Products',
        'route'   =>
            [
                'port' => 'acp',
                'as'   => 'auth::users.products',
                'url'  => 'auth/users/{user}/products',
            ],
        'handler' => [
            'type'   => 'block',
            'config' => [
                'component'    => 'relation_table',
                'page_buttons' => [
                    'attach' => [
                        'text' => 'Add New',
                        'href' => 'relation/attach',
                    ],
                ],
                'parent'       => ['model' => UserModel::class, 'id' => '{request.route.parameters.user}'],
                'relation'     => 'products',
                'columns'      => ['id', 'name'],
            ],
        ],
    ]
];
