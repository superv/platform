<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\Model\DropletCollection;

class LocateResourceJob
{
    private $namespace;

    private $type;

    public function __construct($namespace, $type = null)
    {
        $this->namespace = $namespace;
        $this->type = $type;
    }

    public function handle(DropletCollection $droplets)
    {
        if (! str_is('*::*', $this->namespace)) {
            return null;
        }
        list($droplet, $resource) = explode('::', $this->namespace);
        $droplet = $droplets->get($droplet);

        $location = base_path($droplet->getPath().'/resources/'.str_plural($this->type).'/'.$resource.'.'.$this->type);

        return $location;
    }
}
