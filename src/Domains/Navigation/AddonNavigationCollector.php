<?php

namespace SuperV\Platform\Domains\Navigation;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonCollection;

class AddonNavigationCollector implements Collector
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
        $this->addons->map(function (Addon $addon) use ($slug) {
            $menu = config($addon->slug().'::navigation.'.$slug);
            if ($menu && ! empty($menu)) {
                $this->sections->put($addon->slug(), collect($menu));
            }
        });

        return $this->sections;
    }
}