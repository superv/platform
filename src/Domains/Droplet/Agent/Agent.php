<?php

namespace SuperV\Platform\Domains\Droplet\Agent;

use SuperV\Platform\Domains\Droplet\Droplet;

class Agent extends Droplet
{
    protected $features;

    public function getFeature($key)
    {
        if (!$feature = array_get($this->features, $key)){
            throw new \Exception("Feature <{$key}> not found on agent " . $this->getName());
        }

        return $feature;
    }
}
