<?php

namespace SuperV\Platform\Support;

trait Fireable
{
    /**
     * Dispatch the event with the given arguments.
     *
     * @return void
     */
    public static function fire()
    {
        return event(new static(...func_get_args()));
    }
}
