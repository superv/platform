<?php

namespace SuperV\Platform\Domains\Resource\Builder;

class Relations
{
    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}