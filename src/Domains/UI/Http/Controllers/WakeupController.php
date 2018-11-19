<?php

namespace SuperV\Platform\Domains\UI\Http\Controllers;

use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Http\Controllers\BaseApiController;
use SuperV\Platform\Support\Composer\Composable;

class WakeupController extends BaseApiController
{
    public function get($uuid)
    {
        if ($config = cache('sv/wake/'.$uuid)) {
            if ($object = unserialize($config)) {
                if ($object instanceof Composable) {
                    return ['data' => $object->compose()];
                } else {
                    throw PlatformException::fail('Object is not composable');
                }
            }
        }

        return null;
    }
}