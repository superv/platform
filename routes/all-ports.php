<?php

use SuperV\Platform\Http\Controllers\AuthController;
use SuperV\Platform\Http\Controllers\DataController;

return [
    'data/init'  => DataController::class.'@init',
    'data/nav'   => DataController::class.'@nav',
    'post@login' => AuthController::class.'@login',
    'platform' => function () {
        return 'SuperV Platform @'.Current::port()->slug();
    },
];
