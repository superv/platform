<?php

use SuperV\Platform\Domains\UI\Http\Controllers\WakeupController;
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

    'GET@'.'sv/wake/{uuid}' => WakeupController::at('get'),
];
