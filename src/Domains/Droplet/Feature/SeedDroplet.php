<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\Droplets;
use SuperV\Platform\Domains\Feature\Feature;

class SeedDroplet extends Feature
{
    private $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function handle(Droplets $droplets)
    {
        if (!$droplet = $droplets->withSlug($this->slug)) {
            throw new ModelNotFoundException("Droplet not found {$this->slug}");
        }

        $droplet = $droplet->newDropletInstance();

        $seeders = $droplet->getSeeders();

        foreach ($seeders as $seeder) {
            app()->call([app($seeder), 'seed']);
        }
    }
}