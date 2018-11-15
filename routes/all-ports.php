<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FormsController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Http\Controllers\AuthController;
use SuperV\Platform\Http\Controllers\DataController;

return [
    'data/init'   => DataController::class.'@init',
    'data/nav'    => DataController::class.'@nav',
    'data/navold' => DataController::class.'@navold',
    'post@login'  => [
        'uses' => AuthController::class.'@login',
    ],
    'platform'    => function () {
        return 'SuperV Platform @'.Current::port()->slug();
    },

    'ANY@'.'sv/forms/{form}' => FormsController::at('post'),

    'GET@'.'sv/res/{resource}'           => ResourceController::at('index'),
    'GET@'.'sv/res/{resource}/create'    => ResourceController::at('create'),
    'GET@'.'sv/res/{resource}/{id}/edit' => ResourceController::at('edit'),
    'GET@'.'sv/tables/{uuid}'            => ResourceController::at('table'),
];
