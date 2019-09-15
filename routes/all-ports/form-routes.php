<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;

return [

    'sv/forms/{uuid}/{entry}'         => [
        'as'    => 'resource.forms.edit',
        'uses'  => FormController::at('edit'),
        'where' => ['entry' => '[0-9]*'],
    ],
    'POST@'.'sv/forms/{uuid}/{entry}' => [
        'as'    => 'resource.forms.update',
        'uses'  => FormController::at('update'),
        'where' => ['entry' => '[0-9]*'],
    ],

    'sv/forms/{namespace}/{name}' => [
        'as'   => 'sv::forms.show',
        'uses' => FormController::at('show'),
    ],

    'sv/forms/{uuid}'                               => [
        'as'   => 'resource.forms.create',
        'uses' => FormController::at('create'),
    ],

    'POST@'.'sv/forms/{uuid}'                       => [
        'as'   => 'resource.forms.store',
        'uses' => FormController::at('store'),
    ],

    'ANY@'.'sv/forms/{uuid}/fields/{field?}/{rpc?}' => FormController::at('fields'),
];
