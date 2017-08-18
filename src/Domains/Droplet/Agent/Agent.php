<?php namespace SuperV\Platform\Domains\Droplet\Agent;

use SuperV\Platform\Domains\Droplet\Droplet;

class Agent extends Droplet
{
    protected $features;

    public function getFeature($feature)
    {
        return array_get($this->features, $feature);
    }
}