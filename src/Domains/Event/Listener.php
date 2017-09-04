<?php

namespace SuperV\Platform\Domains\Event;

use Illuminate\Foundation\Bus\DispatchesJobs;

class Listener
{
    use DispatchesJobs;

    public function handle($eventName, array $data)
    {
        if (str_is('*::*.*', $eventName)) {
            $eventName = last(explode('.', $eventName));
        }
        if (method_exists($this, $eventName)) {
            call_user_func_array([$this, $eventName], $data);
        }
    }
}
