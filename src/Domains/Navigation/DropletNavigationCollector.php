<?php

namespace SuperV\Platform\Domains\Navigation;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonCollection;

class DropletNavigationCollector implements Collector
{
    /**
     * @var \SuperV\Platform\Domains\Addon\AddonCollection
     */
    protected $addons;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $sections;

    public function __construct(AddonCollection $addons, Collection $sections)
    {
        $this->addons = $addons;
        $this->sections = $sections;
    }

    public function collect(string $slug): Collection
    {
        $this->addons->map(function (Addon $droplet) use ($slug) {
            $menu = config($droplet->slug().'::navigation.'.$slug);
            if ($menu && !empty($menu)) {
                $this->sections->put($droplet->slug(), collect($menu));
            }
        });

        return $this->sections;
    }
}