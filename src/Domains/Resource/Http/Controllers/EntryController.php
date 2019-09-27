<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class EntryController extends BaseApiController
{
    use ResolvesResource;

//    public function show()
//    {
//        $this->resolveResource();
//
//        return ['data' => ['entry' => sv_compose($this->entry)]];
//    }
}