<?php

use SuperV\Platform\Http\Controllers\AuthController;
use SuperV\Platform\Http\Controllers\DataController;
use SuperV\Platform\Http\Controllers\FormsController;
use SuperV\Platform\Http\Controllers\ResourceController;

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

    'POST@'.'sv/forms/{form}' => FormsController::at('store'),

    'GET@'.'sv/res/{resource}'           => ResourceController::at('index'),
    'GET@'.'sv/res/{resource}/create'    => ResourceController::at('create'),
    'GET@'.'sv/res/{resource}/{id}/edit' => ResourceController::at('edit'),
    'GET@'.'sv/tables/{uuid}'            => ResourceController::at('data'),
];
