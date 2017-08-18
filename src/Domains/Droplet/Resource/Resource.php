<?php namespace SuperV\Platform\Domains\Droplet\Resource;

use SuperV\Platform\Domains\Droplet\Droplet;

class Resource
{
    protected $type;

    protected $droplet;

    public function __construct(Droplet $droplet)
    {
        $this->droplet = $droplet;
    }
}