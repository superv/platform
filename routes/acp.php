<?php

use SuperV\Platform\Domains\Auth\Http\Controllers\LoginController;
use SuperV\Platform\Domains\Auth\Http\Controllers\RegisterController;
use SuperV\Platform\Domains\Auth\Http\Controllers\UsersController;

return [

    'auth/users/{id}/edit' => [
        'as'   => 'auth::users.edit',
        'uses' => UsersController::class.'@edit',
    ],
    'auth/users/create'    => [
        'as'   => 'auth::users.create',
        'uses' => UsersController::class.'@create',
    ],
    'auth/users/index' => [
        'as'   => 'auth::users.index',
        'uses' => UsersController::class.'@index',
    ],
    'get@login'          => [
        'as'   => 'auth::login',
        'uses' => LoginController::class.'@show',
    ],
    'post@login'         => LoginController::class.'@login',
    'get@register'       => [
        'as'   => 'auth::register',
        'uses' => RegisterController::class.'@show',
    ],
    'post@auth/register' => RegisterController::class.'@register',
    'logout'             => [
        'as'   => 'auth::logout',
        'uses' => LoginController::class.'@logout',
    ],

    'platform/entries/{ticket}/delete' => [
        'as'   => 'superv::entries.delete',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\DeleteEntryController@index',
        'port' => 'acp',
    ],
    'platform/entries/{ticket}/edit'   => [
        'as'   => 'superv::entries.edit',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\EditEntryController@index',
        'port' => 'acp',
    ],
    'get@platform/entries/{entry}'     => [
        'as'   => 'superv::entries.show',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\EntriesController'.'@show',
        'port' => 'acp',
    ],
    'patch@platform/entries/{entry}'   => [
        'as'   => 'superv::entries.show',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\EntriesController'.'@patch',
        'port' => 'acp',
    ],

    'platform/entries/{entry}/relations/{relation}/options' => [
        'as'   => 'superv::entries.relations.options',
        'uses' => 'SuperV\Platform\Http\Controllers\Entry\Relations\OptionsController@show',
        'port' => 'acp',
    ],
];