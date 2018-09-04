<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Platform\Domains\Auth\Users;

class BaseApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (app()->environment() === 'local') {
            $user = $this->request->get('user');
            if ($user = 'root') {
                auth()->onceUsingId(Users::withEmail("{$user}@superv.io")->id);
            }
        }

        $this->middleware('auth:superv-api');
    }
}