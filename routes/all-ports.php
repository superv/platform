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

    'POST@'.'sv/forms/{form}'                  => FormsController::at('store'),
    'GET@'.'sv/resources/{resource}/{id}/view' => ResourceController::at('view'),
];
