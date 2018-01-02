<?php

use SuperV\Platform\Domains\Auth\Http\Controllers\LoginController;
use SuperV\Platform\Domains\Auth\Http\Controllers\RegisterController;
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
    ],
    'get@login'            => [
        'as'   => 'auth::login',
        'uses' => LoginController::at('show'),
    ],
    'post@login'           => LoginController::at('login'),
    'get@register'         => [
        'as'   => 'auth::register',
        'uses' => RegisterController::at('show'),
    ],
    'post@auth/register'   => RegisterController::at('register'),
    'logout'               => [
        'as'   => 'auth::logout',
        'uses' => LoginController::at('logout'),
    ],
];