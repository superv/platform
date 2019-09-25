<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;

return [

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

];
