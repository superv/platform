<?php

use SuperV\Platform\Domains\Auth\Http\Controllers\UsersController;

return [
    'auth/users/{id}/edit' => [
        'as'   => 'auth::users.edit',
        'uses' => UsersController::at('edit'),
    ],
    'auth/users/create'    => [
        'as'   => 'auth::users.create',
        'uses' => UsersController::at('create'),
    ],
    'auth/users/index'     => [
        'as'   => 'auth::users.index',
        'uses' => UsersController::at('index'),
    ]
];