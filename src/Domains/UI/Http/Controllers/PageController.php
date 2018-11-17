<?php

namespace SuperV\Platform\Domains\UI\Http\Controllers;

use SuperV\Platform\Http\Controllers\BaseApiController;

class PageController extends BaseApiController
{
    public function get($uuid)
    {
        return ['data' => [
           'page' => $uuid
        ]];
    }
}