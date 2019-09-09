<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;

return [
    'sv/forms'                                      => FormController::at('handle'),
    'sv/forms/{uuid}'                               => [
        'as'   => 'resource.forms.create',
        'uses' => FormController::at('create'),
    ],
    'sv/forms/{uuid}/{entry}'                       => [
        'as'   => 'resource.forms.edit',
        'uses' => FormController::at('edit'),
    ],
    'POST@'.'sv/forms/{uuid}'                       => [
        'as'   => 'resource.forms.store',
        'uses' => FormController::at('store'),
    ],
    'POST@'.'sv/forms/{uuid}/{entry}'               => [
        'as'   => 'resource.forms.update',
        'uses' => FormController::at('update'),
    ],
    'ANY@'.'sv/forms/{uuid}/fields/{field?}/{rpc?}' => FormController::at('fields'),
];
