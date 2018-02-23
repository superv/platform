<?php

namespace SuperV\Platform\Domains\Port;

use Illuminate\Foundation\Events\Dispatchable;

class PortDetectedEvent
{
    use Dispatchable;

    /** @var \SuperV\Platform\Domains\Port\Port */
    public $port;

    public function __construct($port)
    {
        $this->port = $port;
    }
}