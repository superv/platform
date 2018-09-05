<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Platform\Domains\Auth\Users;

class BaseApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (app()->environment() === 'local' && ! $this->request->hasHeader('authorization')) {
            $user = $this->request->get('user', 'root');
            auth()->onceUsingId(Users::withEmail("{$user}@superv.io")->id);
        }

        $this->middleware('auth:superv-api');
    }
}