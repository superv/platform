<?php namespace SuperV\Platform\Domains\Droplet\Types;

use SuperV\Platform\Support\Collection;

class PortCollection extends Collection
{
    public function byHostname($hostname)
    {
        /** @var \SuperV\Platform\Domains\Droplet\Types\Port $port */
        foreach ($this->items as $port) {
            if ($port->getHostname() == $hostname) {
                return $port;
            }
        }
    }
}