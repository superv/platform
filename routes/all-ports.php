<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ActionController;
use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;
use SuperV\Platform\Domains\Resource\Http\Controllers\LookupController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceCreateController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceUpdateController;
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

    'POST@'.'sv/res/{resource}/{id}/rel/{relation}'    => RelationController::at('request'),
    'POST@'.'sv/res/{resource}/{id}/{relation}/attach' => RelationController::at('attach'),
    'POST@'.'sv/res/{resource}/{id}/{relation}/detach' => RelationController::at('detach'),

    'sv/res/{resource}/{id}/{relation}/table/{data?}' => [
        'as'   => 'relation.index',
        'uses' => RelationIndexController::class,
    ],

    'sv/res/{resource}/{id}/{relation}/lookup/{data?}' => LookupController::class,

    'GET@'.'sv/res/{resource}/create'    => ResourceController::at('create'),
    'GET@'.'sv/res/{resource}/{id}/edit' => ResourceController::at('edit'),

    'POST@'.'sv/res/{resource}/{id}' => [
        'as'   => 'resource.update',
        'uses' => ResourceUpdateController::class,
    ],

    'POST@'.'sv/res/{resource}' => [
        'as'   => 'resource.create',
        'uses' => ResourceCreateController::class,
    ],

    'GET@'.'sv/res/{resource}/{data?}' => [
        'as'   => 'resource.index',
        'uses' => ResourceIndexController::class,
    ],

    'GET@'.'sv/wake/{uuid}' => WakeupController::at('get'),
    'GET@'.'sv/act/{uuid}'  => ActionController::at('get'),
    'GET@'.'sv/pag/{uuid}'  => PageController::at('get'),
];
