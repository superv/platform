<?php

namespace SuperV\Platform\Traits;

use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Port\Port;

trait HasPort
{
    public function port()
    {
        return $this->belongsTo(DropletModel::class, 'port_id');
    }

    /** @return DropletModel */
    public function getPortEntry()
    {
        return $this->port;
    }

    /** @return Port */
    public function getPort()
    {
        return app(DropletFactory::class)->create($this->getPortEntry());
    }

    public function getPortSlug()
    {
        return $this->getPortEntry()->getSlug();
    }
}