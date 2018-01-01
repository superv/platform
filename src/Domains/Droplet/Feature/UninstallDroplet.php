<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\Droplets;
use SuperV\Platform\Domains\Droplet\Events\DropletUninstallingEvent;
use SuperV\Platform\Domains\Feature\Feature;

class UninstallDroplet extends Feature
{
    private $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function handle(Droplets $droplets)
    {
        $droplet = $droplets->must("Droplet {$this->slug} not found")
                           ->withSlug($this->slug);

        event(new DropletUninstallingEvent($droplet));

        $droplet->delete();

        return true;
    }
}
