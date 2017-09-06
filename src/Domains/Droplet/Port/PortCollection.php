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

    public function bySlug($slug)
    {
        /** @var \SuperV\Platform\Domains\Droplet\Port\Port $port */
        foreach ($this->items as $port) {
            if ($port->getSlug() == $slug) {
                return $port;
            }
        }
    }
}
