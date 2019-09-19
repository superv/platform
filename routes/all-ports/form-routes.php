<?php

use SuperV\Platform\Domains\Resource\Form\v2\FormController as FormV2Controller;
use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;

return [

    'sv/forms-v2/{identifier}/{entry?}' => [
        'as'   => 'sv::forms.v2.show',
        'uses' => FormV2Controller::at('show'),
    ],

    'sv/frm/{identifier}' => [
        'as'   => 'sv::forms.show',
        'uses' => FormController::at('show'),
    ],

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

    //    'sv/forms/{namespace}/{name}' => [
    //        'as'   => 'sv::forms.show',
    //        'uses' => FormController::at('show'),
    //    ],

    'sv/forms/{uuid}' => [
        'as'   => 'resource.forms.create',
        'uses' => FormController::at('create'),
    ],

    'POST@'.'sv/forms/{uuid}' => [
        'as'   => 'resource.forms.store',
        'uses' => FormController::at('store'),
    ],

    'ANY@'.'sv/forms/{uuid}/fields/{field?}/{rpc?}' => FormController::at('fields'),
];
