<?php

namespace SuperV\Platform\Domains\Port;

use Illuminate\Foundation\Events\Dispatchable;

class PortDetectedEvent
{
    use Dispatchable;

    public $port;

    public function __construct($port)
    {
        $this->port = $port;
    }
}