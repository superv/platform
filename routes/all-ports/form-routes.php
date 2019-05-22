<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;

return [
    'sv/forms'                => FormController::at('handle'),
    'sv/forms/{uuid}'         => FormController::at('show'),
    'POST@'.'sv/forms/{uuid}' => FormController::at('post'),
    'ANY@'.'sv/forms/{uuid}/fields/{field?}/{rpc?}' => FormController::at('fields'),
];