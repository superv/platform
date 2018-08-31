<?php

namespace SuperV\Platform\Domains\Navigation;

use Closure;
use Illuminate\Support\Collection;

class Navigation
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
     * @var array
     */
    protected $navigation;

    /**
     * @var \SuperV\Platform\Domains\Navigation\Collector
     */
    protected $collector;

    public function __construct(Collector $collector)
    {
        $this->collector = $collector;
    }

    public function slug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    protected function build()
    {

        $this->navigation = [
            'slug'     => $this->slug,
            'sections' => $this->collector->collect($this->slug)
                                          ->map(Closure::fromCallable([$this, 'buildMenus']))
                                          ->values()
                                          ->flatten(1)
                                          ->all(),
        ];
    }

    protected function buildMenus(Collection $menuList)
    {
        return $menuList->map(function ($menu) {
            return $menu instanceof Section ? $menu->build() : $menu;
        })->all();
    }

    public function get()
    {
        $this->build();

        return $this->navigation;
//        dd($this->sections);
    }
}