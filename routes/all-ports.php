<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ActionController;
use SuperV\Platform\Domains\Resource\Http\Controllers\FormController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Domains\Resource\Http\Controllers\TableController;
use SuperV\Platform\Domains\UI\Http\Controllers\PageController;
use SuperV\Platform\Domains\UI\Http\Controllers\WakeupController;
use SuperV\Platform\Http\Controllers\AuthController;
use SuperV\Platform\Http\Controllers\DataController;

return [
    'data/init'                                        => DataController::class.'@init',
    'data/nav'                                         => DataController::class.'@nav',
    'data/navold'                                      => DataController::class.'@navold',
    'post@login'                                       => [
        'uses' => AuthController::class.'@login',
    ],
    'platform'                                         => function () {
        return 'SuperV Platform @'.Current::port()->slug();
    },
    'POST@'.'sv/res/{resource}/{id}/{relation}/attach' => RelationController::at('attach'),
    'GET@'.'sv/res/{resource}/{id}/{relation}/lookup'  => RelationController::at('lookup'),
    'GET@'.'sv/res/{resource}/{id}/{relation}/lookup/data'  => RelationController::at('lookupData'),
    'GET@'.'sv/res/{resource}'                         => ResourceController::at('index'),
    'GET@'.'sv/res/{resource}/create'                  => ResourceController::at('create'),
    'GET@'.'sv/res/{resource}/{id}/edit'               => ResourceController::at('edit'),
    'ANY@'.'sv/forms/{form}'                           => FormController::at('post'),
    'GET@'.'sv/tables/{uuid}/config'                   => TableController::at('config'),
    'GET@'.'sv/tables/{uuid}/data'                     => TableController::at('data'),
    'GET@'.'sv/wake/{uuid}'                            => WakeupController::at('get'),
    'GET@'.'sv/act/{uuid}'                             => ActionController::at('get'),
    'GET@'.'sv/pag/{uuid}'                             => PageController::at('get'),
];
