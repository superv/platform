<?php namespace SuperV\Platform\Domains\Droplet\Types;

use SuperV\Platform\Domains\Droplet\Droplet;

class Port extends Droplet
{
    protected $hostname;

    protected $type = 'port';

    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getHostname()
    {
        return $this->hostname;
    }
}