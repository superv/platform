<?php

namespace SuperV\Platform\Domains\Navigation;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Authorization\Haydar;

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

    /**
     * @var \SuperV\Platform\Domains\Authorization\Haydar
     */
    protected $haydar;

    public function __construct(Collector $collector, Haydar $haydar)
    {
        $this->collector = $collector;
        $this->haydar = $haydar;
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
                                          ->map(Closure::fromCallable([$this, 'buildSections']))
                                          ->values()
                                          ->flatten(1)
                                          ->all(),
        ];
    }

    protected function buildSections(Collection $sections)
    {
        return $sections->map(function ($section) {
            /** @not-test-block */
            if (is_array($section)) {
                $section = Section::make($section);
            }

            return $section->build();
        })->filter()->all();
    }

    public function get()
    {
        $this->build();

        return $this->navigation;
    }
}