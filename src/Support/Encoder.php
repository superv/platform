<?php

namespace SuperV\Platform\Support;

use Hashids\Hashids;
use Jenssegers\Optimus\Optimus;

class Encoder
{
    public static function toNumber($value)
    {
        return app(Optimus::class)->encode($value);
    }

    public static function toString($value, $length = 32)
    {
        $salt = config('app.key');

        return (new Hashids($salt, $length))->encode($value);
    }
}