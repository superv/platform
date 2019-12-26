<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\FieldController;

return [
    'sv/fld/{resource}/{entry}/{field}/{data?}' => [
        'as'   => 'resource.entry.fields',
        'uses' => FieldController::at('index'),
    ],

    'sv/fld/act/{resource}/{entry}/{field}/create' => [
        'as'   => 'resource.entry.field_actions.create',
        'uses' => FieldController::at('create'),
    ],

    'POST@'.'sv/fld/act/{resource}/{entry}/{field}' => [
        'as'   => 'resource.entry.field_actions.store',
        'uses' => FieldController::at('store'),
    ],
];