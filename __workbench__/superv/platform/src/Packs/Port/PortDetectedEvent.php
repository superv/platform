<?php

namespace SuperV\Platform\Packs\Port;

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