<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Platform\Domains\Auth\Users;

class BaseApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (app()->environment() === 'local') {
            auth()->onceUsingId(Users::withEmail('root@superv.io')->id);
        }

        $this->middleware('auth:superv-api');
    }
}