<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FieldController;

return [

    'ANY@'.'sv/fld/types/{type}/{route}' => [
        'as'   => 'sv::fields.types',
        'uses' => FieldController::at('route'),
    ],

    'sv/fld/{resource}/{entry}/{field}/{data?}' => [
        'as'   => 'resource.entry.fields',
        'uses' => FieldController::at('index'),
    ],

    'sv/fld/act/{resource}/{entry}/{field}/create' => [
        'as'   => 'resource.entry.field_actions.create',
        'uses' => FieldController::at('create'),
    ],

    'sv/fld/rpc/{resource}/{entry}/{field}/{rpc}' => [
        'as'   => 'resource.entry.field_rpc',
        'uses' => FieldController::at('rpc'),
    ],

    'POST@'.'sv/fld/act/{resource}/{entry}/{field}' => [
        'as'   => 'resource.entry.field_actions.store',
        'uses' => FieldController::at('store'),
    ],
];