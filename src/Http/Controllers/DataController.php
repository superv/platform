<?php

namespace SuperV\Platform\Http\Controllers;

use Current;
use SuperV\Platform\Domains\Resource\Nav\Nav;

class DataController extends BaseApiController
{
    public function init()
    {
        return [
            'data' => [
                'user' => auth()->user(),
            ],
        ];
    }

    public function nav()
    {
        $portNav = Current::port()->getNavigationSlug();

        return [
            'data' => $portNav ? [
                'nav' => Nav::get($portNav)->compose(),
            ] : ['message' => 'Current port has no navigation'],
        ];
    }

}