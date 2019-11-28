<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

class Relations
{
    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}