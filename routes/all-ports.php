<?php

use SuperV\Platform\Http\Controllers\AuthController;
use SuperV\Platform\Http\Controllers\DataController;
use SuperV\Platform\Http\Controllers\FormsController;
use SuperV\Platform\Http\Controllers\ResourceController;

return [
    'data/init'  => DataController::class.'@init',
    'data/nav'   => DataController::class.'@nav',
    'post@login' => [
        'uses' => AuthController::class.'@login',
    ],
    'platform'   => function () {
        return 'SuperV Platform @'.Current::port()->slug();
    },

    'POST@'.'sv/forms/{form}' => FormsController::at('store'),

    'GET@'.'sv/resources/{resource}/create'    => ResourceController::at('create'),
    'GET@'.'sv/resources/{resource}/{id}/edit' => ResourceController::at('edit'),
];
