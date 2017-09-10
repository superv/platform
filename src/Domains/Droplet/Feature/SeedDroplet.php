<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use App\Console\Kernel;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
use SuperV\Platform\Domains\Feature\Feature;

class SeedDroplet extends Feature
{
    private $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function handle(Kernel $console, DropletFactory $factory)
    {
        $droplet = $factory->fromSlug($this->slug);

        $seeders = $droplet->getSeeders();

        foreach ($seeders as $seeder) {
            $this->dispatch(new $seeder);
        }
    }
}