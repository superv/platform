<?php

namespace SuperV\Platform\Domains\Navigation;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Support\Collection;

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
     * @var \SuperV\Platform\Support\Collection
     */
    protected $sections;

    /**
     * @var array
     */
    protected $navigation;

    public function __construct(DropletCollection $droplets, Collection $sections)
    {
        $this->droplets = $droplets;
        $this->sections = $sections;
    }

    public function slug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    protected function compose()
    {
        $this->droplets->map(function (Droplet $droplet) {
            $menu = config($droplet->slug().'::navigation.'.$this->slug);
            $this->sections->put($droplet->slug(), collect($menu));
        });
    }

    protected function build()
    {
        $this->navigation = [
            'slug'     => $this->slug,
            'sections' => $this->sections->map(
                function ($dropletMenus) {
                    return $dropletMenus->map(function ($menu) {
                        return $menu instanceof Section ? $menu->build() : $menu;
                    })->all();
                })->values()->flatten(1)->all(),
        ];
    }

    public function get()
    {
        $this->compose();
        $this->build();

        return $this->navigation;
//        dd($this->sections);
    }
}