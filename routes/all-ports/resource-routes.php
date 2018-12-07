<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceCreateController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceUpdateController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceViewController;

return [
    /**
     * Resource Management
     */
    'GET@'.'sv/res/{resource}/create'    => ResourceController::at('create'),
    'GET@'.'sv/res/{resource}/{id}/edit' => ResourceController::at('edit'),

    'sv/res/{resource}/{id}/view' => [
        'as'   => 'resource.view',
        'uses' => ResourceViewController::class,
    ],

    'POST@'.'sv/res/{resource}/{id}' => [
        'as'   => 'resource.update',
        'uses' => ResourceUpdateController::class,
    ],

    'POST@'.'sv/res/{resource}' => [
        'as'   => 'resource.create',
        'uses' => ResourceCreateController::class,
    ],

    'GET@'.'sv/res/{resource}' => [
        'as'   => 'resource.index',
        'uses' => ResourceIndexController::at('page'),
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