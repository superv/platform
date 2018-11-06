<?php

namespace SuperV\Platform\Http\Controllers;

use Current;
use SuperV\Platform\Domains\Navigation\Navigation;
use SuperV\Platform\Domains\Resource\Nav\Nav;

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
                'nav' => Nav::get($portNav)->compose(),
            ] : ['message' => 'Current port has no navigation'],
        ];
    }

    public function navold(Navigation $nav)
    {
        $portNav = Current::port()->getNavigationSlug();

        return [
            'data' => $portNav ? [
                'navigation' => $nav->slug($portNav)->get(),
            ] : ['message' => 'Current port has no navigation'],
        ];
    }
}