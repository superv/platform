<?php


use SuperV\Platform\Http\Controllers\BroadcastController;

return [
    'ANY@broadcasting/auth' => BroadcastController::class . '@authenticate',
];