<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\LookupController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationCreateController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\RelationUpdateController;

return [

    'POST@'.'sv/res/{resource}/{entry}/rel/{relation}' => RelationController::at('request'),

    'POST@'.'sv/res/{resource}/{entry}/rel/{relation}/attach'           => [
        'as'   => 'relation.attach',
        'uses' => RelationController::at('attach'),
    ],
    'POST@'.'sv/res/{resource}/{entry}/rel/{relation}/detach/{related}' => [
        'as'   => 'relation.detach',
        'uses' => RelationController::at('detach'),
    ],

    'sv/res/{resource}/{entry}/rel/{relation}/lookup/{data?}' => [
        'as'   => 'relation.lookup',
        'uses' => LookupController::class,
    ],

    'sv/res/{resource}/{entry}/rel/{relation}/create'  => [
        'as'   => 'relation.create',
        'uses' => RelationCreateController::at('create'),
    ],
    'POST@'.'sv/res/{resource}/{entry}/rel/{relation}' => [
        'as'   => 'relation.store',
        'uses' => RelationCreateController::at('store'),
    ],

    'sv/res/{resource}/{entry}/rel/{relation}/edit' => [
        'as'   => 'relation.edit',
        'uses' => RelationUpdateController::at('edit'),
    ],

    'sv/res/{resource}/{entry}/rel/{relation}/{data?}' => [
        'as'   => 'relation.index',
        'uses' => RelationIndexController::class,
    ],
];