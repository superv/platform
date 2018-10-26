<?php

namespace SuperV\Platform\Http\Controllers;

use Current;
use SuperV\Platform\Domains\Navigation\Navigation;

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

    public function nav(Navigation $nav)
    {
        $portNav = Current::port()->getNavigationSlug();

        return [
            'data' => $portNav ? [
                'navigation' => $nav->slug($portNav)->get(),
            ] : ['message' => 'Current port has no navigation'],
        ];
    }
}