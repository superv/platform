<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ActionController;
use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;
use SuperV\Platform\Domains\Resource\Http\Controllers\LookupController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationCreateController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationUpdateController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceCreateController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceUpdateController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceViewController;
use SuperV\Platform\Domains\UI\Http\Controllers\PageController;
use SuperV\Platform\Domains\UI\Http\Controllers\WakeupController;
use SuperV\Platform\Http\Controllers\AuthController;
use SuperV\Platform\Http\Controllers\DataController;

return [
    'data/init'   => DataController::class.'@init',
    'data/nav'    => DataController::class.'@nav',
    'data/navold' => DataController::class.'@navold',
    'post@login'  => [
        'uses' => AuthController::class.'@login',
    ],
    'platform'    => function () {
        return 'SuperV Platform @'.Current::port()->slug();
    },

    'POST@'.'sv/res/{resource}/{id}/rel/{relation}' => RelationController::at('request'),

    'POST@'.'sv/res/{resource}/{id}/rel/{relation}/attach'           => [
        'as'   => 'relation.attach',
        'uses' => RelationController::at('attach'),
    ],
    'POST@'.'sv/res/{resource}/{id}/rel/{relation}/detach/{related}' => [
        'as'   => 'relation.detach',
        'uses' => RelationController::at('detach'),
    ],

    'sv/res/{resource}/{id}/rel/{relation}/lookup/{data?}' => [
        'as'   => 'relation.lookup',
        'uses' => LookupController::class,
    ],

    'sv/res/{resource}/{id}/rel/{relation}/create'  => [
        'as'   => 'relation.create',
        'uses' => RelationCreateController::at('create'),
    ],
    'POST@'.'sv/res/{resource}/{id}/rel/{relation}' => [
        'as'   => 'relation.store',
        'uses' => RelationCreateController::at('store'),
    ],

    'sv/res/{resource}/{id}/rel/{relation}/edit' => [
        'as'   => 'relation.edit',
        'uses' => RelationUpdateController::at('edit'),
    ],

    'sv/res/{resource}/{id}/rel/{relation}/{data?}' => [
        'as'   => 'relation.index',
        'uses' => RelationIndexController::class,
    ],

    /**
     * Resource Management
     */
    'GET@'.'sv/res/{resource}/create'               => ResourceController::at('create'),
    'GET@'.'sv/res/{resource}/{id}/edit'            => ResourceController::at('edit'),

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

    'GET@'.'sv/wake/{uuid}' => WakeupController::at('get'),
];
