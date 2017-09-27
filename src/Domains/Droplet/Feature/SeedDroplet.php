<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Feature\Feature;

class SeedDroplet extends Feature
{
    private $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function handle(DropletFactory $factory)
    {
        $droplet = $factory->fromSlug($this->slug);

        $seeders = $droplet->getSeeders();

        foreach ($seeders as $seeder) {
            app($seeder)->seed();
        }
    }
}