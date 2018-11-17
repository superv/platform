<?php

namespace SuperV\Platform\Domains\UI\Http\Controllers;

use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Http\Controllers\BaseApiController;
use SuperV\Platform\Support\Composer\Composable;

class ComponentController extends BaseApiController
{

    public function wakeup($uuid)
    {
        if ($config = cache('sv/cmp/'.$uuid)) {
           if ( $object = unserialize($config)) {
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