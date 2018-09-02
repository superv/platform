<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Platform\Domains\Auth\Users;
use SuperV\Platform\Domains\Navigation\Section;

class BaseApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (app()->environment() === 'local') {
            $email = $this->request->get('email', 'root@superv.io');
            auth()->onceUsingId(Users::withEmail($email)->id);
        }

        $this->middleware('auth:superv-api');
    }
}