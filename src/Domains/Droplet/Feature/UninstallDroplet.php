<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Droplet\Model\Droplets;
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
        abort_unless($droplet = $droplets->withSlug($this->slug), 404);

        return true;
    }
}
