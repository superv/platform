<?php

namespace SuperV\Platform\Support;

use SuperV\Platform\Contracts\Bootable;

class Booter
{
    public static function boot($bootable)
    {
        if ($bootable instanceof Bootable) {
            $bootable->boot();
        }
    }
}