<?php

use SuperV\Platform\Domains\UI\Http\Controllers\WakeupController;
use SuperV\Platform\Http\Controllers\AuthController;
use SuperV\Platform\Http\Controllers\DataController;

return [

    'sv/data/init'  => [
        'as'   => 'sv.data.init',
        'uses' => DataController::class.'@init',
    ],
    'sv/data/nav'   => [
        'as'   => 'sv.data.nav',
        'uses' => DataController::class.'@nav',
    ],
    'post@sv/login' => [
        'as'   => 'sv.login',
        'uses' => AuthController::class.'@login',
    ],
    'sv/platform'   => function () {
        return 'SuperV Platform @'.Current::port()->slug();
    },

    'GET@'.'sv/wake/{uuid}' => WakeupController::at('get'),
];
