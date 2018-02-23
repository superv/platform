<?php

namespace SuperV\Platform\Support;

use Illuminate\Contracts\Bus\Dispatcher;

trait Dispatchable
{
    public static function dispatch()
    {
        return app(Dispatcher::class)->dispatch(new static(...func_get_args()));
    }

}