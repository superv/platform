<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceEditController;
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

    /**
     * Edit
     */

    'GET@'.'sv/res/{resource}/{id}/edit' => [
        'as'    => 'resource.edit',
        'uses'  => ResourceFormController::at('edit'),
        'where' => ['id' => '[0-9]*'],
    ],

    'GET@'.'sv/res/{resource}/{id}/edit-page' => [
        'as'    => 'resource.edit.page',
        'uses'  => ResourceEditController::at('page'),
        'where' => ['id' => '[0-9]*'],
    ],

    /**
     * view
     */

    'GET@'.'sv/res/{resource}/{id}/view-page' => [
        'as'    => 'resource.view.page',
        'uses'  => ResourceViewController::at('page'),
        'where' => ['id' => '[0-9]*'],
    ],

    'GET@'.'sv/res/{resource}/{id}/view' => [
        'as'    => 'resource.view',
        'uses'  => ResourceViewController::at('view'),
        'where' => ['id' => '[0-9]*'],
    ],

    'GET@'.'sv/res/{resource}/{id}' => [
        'uses'  => ResourceViewController::at('page'),
        'where' => ['id' => '[0-9]*'],
    ],

    /**
     *
     */

    'POST@'.'sv/res/{resource}/{id}' => [
        'as'   => 'resource.update',
        'uses' => ResourceFormController::at('update'),
    ],

    'DELETE@'.'sv/res/{resource}/{id}' => [
        'as'   => 'resource.delete',
        'uses' => ResourceIndexController::at('delete'),
    ],

    'POST@'.'sv/res/{resource}/{id}/restore' => [
        'as'   => 'resource.restore',
        'uses' => ResourceIndexController::at('restore'),
    ],

    'ANY@'.'sv/res/{resource}/fields/{field?}/{rpc?}' => [
        'as'   => 'resource.fields',
        'uses' => ResourceController::at('fields'),
    ],

    'GET@'.'sv/res/{resource}' => [
        'as'   => 'resource.index',
        'uses' => ResourceIndexController::at('page'),
    ],

    'ANY@'.'sv/res/{resource}/{id}/actions/{action}' => [
        'as'    => 'resource.entry.actions',
        'uses'  => ResourceIndexController::at('action'),
        'where' => ['id' => '[0-9]*'],
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