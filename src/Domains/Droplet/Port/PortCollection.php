<?php

namespace SuperV\Platform\Domains\Droplet\Port;

use SuperV\Platform\Support\Collection;

class PortCollection extends Collection
{
    public function byHostname($hostname)
    {
        /** @var \SuperV\Platform\Domains\Droplet\Port\Port $port */
        foreach ($this->items as $port) {
            if ($port->getHostname() == $hostname) {
                return $port;
            }
        }
    }

}
