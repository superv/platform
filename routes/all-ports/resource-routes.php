<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceDashboardController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceEntryDashboardController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceViewController;

return [
    /**
     * RESOURCE DASHBOARD
     */
    'GET@'.'sv/res/{resource}/{section?}'      => [
        'as'    => 'resource.dashboard',
        'uses'  => ResourceDashboardController::class,
        'where' => ['section' => '^(|create|all)$'],
    ],

    'GET@'.'sv/res/{resource}/{id}/_view'      => [
        'as'    => 'resource.entry.view',
        'uses'  => ResourceViewController::at('view'),
        'where' => ['id' => '[0-9]*'],
    ],

    /**
     * RESOURCE ENTRY DASHBOARD
     */
    'GET@'.'sv/res/{resource}/{id}/{section?}' => [
        'as'    => 'resource.entry.dashboard',
        'uses'  => ResourceEntryDashboardController::class,
        'where' => ['id' => '[0-9]*'], // , 'section' => '^(|view|edit)$'
    ],

    /**
     * Tables
     */

    'GET@'.'sv/res/{resource}/table/{data?}' => [
        'as'   => 'resource.table',
        'uses' => ResourceIndexController::at('table'),
    ],

    'GET@'.'sv/res/{resource}/table/actions/{action}' => [
        'uses' => ResourceIndexController::at('tableAction'),
    ],

    'POST@'.'sv/res/{resource}/table/actions/{action}' => [
        'uses' => ResourceIndexController::at('tableActionPost'),
    ],



    /**
     * Edit & Update
     */

    //    'GET@'.'sv/res/{resource}/{id}/edit-page' => [
    //        'as'    => 'resource.edit.page',
    //        'uses'  => ResourceEditController::at('page'),
    //        'where' => ['id' => '[0-9]*'],
    //    ],

    /**
     * View
     */

    //    'GET@'.'sv/res/{resource}/{id}/view-page' => [
    //        'as'    => 'resource.view.page',
    //        'uses'  => ResourceViewController::at('page'),
    //        'where' => ['id' => '[0-9]*'],
    //    ],

    //    'GET@'.'sv/res/{resource}/{id}/view' => [
    //        'as'    => 'resource.view',
    //        'uses'  => ResourceViewController::at('view'),
    //        'where' => ['id' => '[0-9]*'],
    //    ],

    //    'GET@'.'sv/res/{resource}/{id}' => [
    //        'uses'  => ResourceViewController::at('page'),
    //        'where' => ['id' => '[0-9]*'],
    //    ],

    /**
     *
     */

    'DELETE@'.'sv/res/{resource}/{id}' => [
        'as'   => 'resource.delete',
        'uses' => ResourceController::at('delete'),
    ],

    'POST@'.'sv/res/{resource}/{id}/restore' => [
        'as'   => 'resource.restore',
        'uses' => ResourceController::at('restore'),
    ],

    //    'ANY@'.'sv/res/{resource}/fields/{field?}/{rpc?}' => [
    //        'as'   => 'resource.fields',
    //        'uses' => ResourceController::at('fields'),
    //    ],

    'ANY@'.'sv/res/{resource}/{id}/actions/{action}' => [
        'as'    => 'resource.entry.actions',
        'uses'  => ResourceIndexController::at('action'),
        'where' => ['id' => '[0-9]*'],
    ],


];
