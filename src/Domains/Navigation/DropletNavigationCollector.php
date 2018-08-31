<?php

namespace SuperV\Platform\Domains\Navigation;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Support\Collection;

class DropletNavigationCollector implements Collector
{
    /**
     * @var \SuperV\Platform\Domains\Droplet\DropletCollection
     */
    protected $droplets;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var \SuperV\Platform\Support\Collection
     */
    protected $sections;

    public function __construct(DropletCollection $droplets, Collection $sections)
    {
        $this->droplets = $droplets;
        $this->sections = $sections;
    }

    public function collect(string $slug): Collection
    {
        $this->droplets->map(function (Droplet $droplet) use ($slug) {
            $menu = config($droplet->slug().'::navigation.'.$slug);

            $this->sections->put($droplet->slug(), collect($menu));
        });

        return $this->sections;
    }
}