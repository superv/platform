<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ActionController;
use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;
use SuperV\Platform\Domains\Resource\Http\Controllers\LookupController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceIndexController;
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

    'POST@'.'sv/res/{resource}/{id}/rel/{relation}'          => RelationController::at('request'),
    'POST@'.'sv/res/{resource}/{id}/{relation}/attach'       => RelationController::at('attach'),
    'POST@'.'sv/res/{resource}/{id}/{relation}/detach'       => RelationController::at('detach'),
    'GET@'.'sv/res/{resource}/{id}/{relation}/table/{data?}' => RelationController::at('table'),

    'sv/res/{resource}/{id}/{relation}/lookup/{data?}' => LookupController::class,

    'GET@'.'sv/res/{resource}/create'    => ResourceController::at('create'),
    'POST@'.'sv/res/{resource}'          => ResourceController::at('store'),
    'GET@'.'sv/res/{resource}/{id}/edit' => ResourceController::at('edit'),
    'POST@'.'sv/res/{resource}/{id}'     => ResourceController::at('update'),

    'GET@'.'sv/res/{resource}/{data?}' => ResourceIndexController::class,

//    'GET@'.'sv/tables/{uuid}/config' => TableController::at('config'),
//    'GET@'.'sv/tables/{uuid}/data'   => TableController::at('data'),
    'GET@'.'sv/wake/{uuid}'            => WakeupController::at('get'),
    'GET@'.'sv/act/{uuid}'             => ActionController::at('get'),
    'GET@'.'sv/pag/{uuid}'             => PageController::at('get'),
];
