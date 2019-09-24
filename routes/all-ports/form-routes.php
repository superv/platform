<?php

use SuperV\Platform\Domains\Resource\Form\v2\FormController as FormV2Controller;
use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;

return [

    'ANY@'.'sv/forms-v2/{identifier}/{entry?}' => [
        'as'   => 'sv::forms.v2.show',
        'uses' => FormV2Controller::at('handle'),
    ],

    'sv/frm/{form}/{entry?}' => [
        'as'   => 'sv::forms.display',
        'uses' => FormController::at('display'),
    ],

    'POST@'.'sv/frm/{form}/{entry?}' => [
        'as'   => 'sv::forms.submit',
        'uses' => FormController::at('submit'),
    ],

    'ANY@'.'sv/frm/{form}/fields/{field?}/{rpc?}' => [
        'as'   => 'sv::forms.fields',
        'uses' => FormController::at('fields'),
    ],

    //    'sv/forms/{uuid}/{entry}'         => [
    //        'as'    => 'resource.forms.edit',
    //        'uses'  => FormController::at('edit'),
    //        'where' => ['entry' => '[0-9]*'],
    //    ],
    //    'POST@'.'sv/forms/{uuid}/{entry}' => [
    //        'as'    => 'resource.forms.update',
    //        'uses'  => FormController::at('update'),
    //        'where' => ['entry' => '[0-9]*'],
    //    ],

    //    'sv/forms/{namespace}/{name}' => [
    //        'as'   => 'sv::forms.show',
    //        'uses' => FormController::at('show'),
    //    ],

    //    'sv/forms/{uuid}' => [
    //        'as'   => 'resource.forms.create',
    //        'uses' => FormController::at('create'),
    //    ],
    //
    //    'POST@'.'sv/forms/{uuid}' => [
    //        'as'   => 'resource.forms.store',
    //        'uses' => FormController::at('store'),
    //    ],


];
