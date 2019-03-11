<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\Middleware\WatchActivity;

class BaseApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('sv.auth:superv-api');
        $this->middleware(WatchActivity::class);
    }
}