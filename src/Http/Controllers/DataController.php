<?php

namespace SuperV\Platform\Http\Controllers;

use Current;
use SuperV\Platform\Domains\Navigation\Navigation;
use SuperV\Platform\Domains\Resource\Nav;

class DataController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:superv-api');
    }

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
                'nav' => Nav::make($portNav)->build()->compose(),
            ] : ['message' => 'Current port has no navigation'],
        ];
    }

    public function nav_old(Navigation $nav)
    {
        $portNav = Current::port()->getNavigationSlug();

        return [
            'data' => $portNav ? [
                'navigation' => $nav->slug($portNav)->get(),
            ] : ['message' => 'Current port has no navigation'],
        ];
    }
}