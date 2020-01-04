<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;

return [
    'sv/frm/{form}/{entry?}' => [
        'as'    => 'sv::forms.display',
        'uses'  => FormController::at('display'),
        'where' => ['entry' => '[0-9]*'],
    ],

    'POST@'.'sv/frm/{form}/{entry?}' => [
        'as'   => 'sv::forms.submit',
        'uses' => FormController::at('submit'),
    ],

    'ANY@'.'sv/frm/{form}/fields/{field?}/{rpc}' => [
        'as'   => 'sv::forms.field_rpc',
        'uses' => FormController::at('fields'),
    ],

    'ANY@'.'sv/frm/{form}/fields/{field?}' => [
        'as'   => 'sv::forms.fields',
        'uses' => FormController::at('fields'),
    ],
];
