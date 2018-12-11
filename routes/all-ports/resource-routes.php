<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceFormController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceViewController;

return [
    /**
     * Resource Management
     */
    'GET@'.'sv/res/{resource}/create' => [
        'as'   => 'resource.create',
        'uses' => ResourceFormController::at('create'),
    ],

    'POST@'.'sv/res/{resource}' => [
        'as'   => 'resource.store',
        'uses' => ResourceFormController::at('store'),
    ],

    'GET@'.'sv/res/{resource}/{id}/edit' => [
        'as'   => 'resource.edit',
        'uses' => ResourceFormController::at('edit'),
    ],

    'POST@'.'sv/res/{resource}/{id}' => [
        'as'   => 'resource.update',
        'uses' => ResourceFormController::at('update'),
    ],

    'sv/res/{resource}/{id}' => [
        'as'    => 'resource.view.page',
        'uses'  => ResourceViewController::at('page'),
        'where' => ['id' => '[0-9]*'],
    ],

    'sv/res/{resource}/{id}/view' => [
        'as'   => 'resource.view',
        'uses' => ResourceViewController::at('view'),
    ],

    'GET@'.'sv/res/{resource}' => [
        'as'   => 'resource.index',
        'uses' => ResourceIndexController::at('page'),
    ],

    'ANY@'.'sv/res/{resource}/{id}/actions/{action}' => [
        'as'   => 'resource.entry.actions',
        'uses' => ResourceIndexController::at('action'),
    ],

    'GET@'.'sv/res/{resource}/table/{data?}' => [
        'as'   => 'resource.index.table',
        'uses' => ResourceIndexController::at('table'),
    ],

    'GET@'.'sv/res/{resource}/table/actions/{action}' => [
        'uses' => ResourceIndexController::at('tableAction'),
    ],

    'POST@'.'sv/res/{resource}/table/actions/{action}' => [
        'uses' => ResourceIndexController::at('tableActionPost'),
    ],
];