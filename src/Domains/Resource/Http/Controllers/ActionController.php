<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Http\Controllers\BaseApiController;

class ActionController extends BaseApiController
{
    public function get($uuid)
    {
        if ($config = cache('sv/act/'.$uuid)) {
            if ($object = unserialize($config)) {
//                return ['data' => $object->getLookupTable()];
            }
        }
    }
}