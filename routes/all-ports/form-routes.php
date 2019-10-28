<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;
use SuperV\Platform\Domains\Resource\Http\Controllers\PublicFormController;

return [

    /**
     *  Private Forms
     */
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

    /**
     *  Public Forms
     *
     */
    'sv/pub/frm/{form}'                           => [
        'as'   => 'sv::public_forms.display',
        'uses' => PublicFormController::at('display'),
    ],

    'POST@'.'sv/pub/frm/{form}' => [
        'as'   => 'sv::public_forms.submit',
        'uses' => PublicFormController::at('submit'),
    ],

    'ANY@'.'sv/pub/frm/{form}/fields/{field?}/{rpc?}' => [
        'as'   => 'sv::public_forms.fields',
        'uses' => PublicFormController::at('fields'),
    ],

];
