<?php

namespace SuperV\Platform\Http\Controllers;

class BaseApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:superv-api');
    }
}