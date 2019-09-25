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

    'GET@'.'sv/res/{resource}/{id}/view' => [
        'as'    => 'resource.entry.view',
        'uses'  => ResourceViewController::at('view'),
        'where' => ['id' => '[0-9]*'],
    ],

    'ANY@'.'sv/res/{resource}/{id}/actions/{action}' => [
        'as'    => 'resource.entry.actions',
        'uses'  => ResourceIndexController::at('action'),
        'where' => ['id' => '[0-9]*'],
    ],

    'DELETE@'.'sv/res/{resource}/{id}' => [
        'as'   => 'resource.entry.delete',
        'uses' => ResourceController::at('delete'),
    ],

    'POST@'.'sv/res/{resource}/{id}/restore'   => [
        'as'   => 'resource.entry.restore',
        'uses' => ResourceController::at('restore'),
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



];
