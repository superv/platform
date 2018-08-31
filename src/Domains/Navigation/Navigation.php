<?php

namespace SuperV\Platform\Domains\Navigation;

use Closure;
use Illuminate\Events\Dispatcher;
use SuperV\Platform\Domains\Authorization\Haydar;

class Navigation implements SectionBag
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

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /** @var \Illuminate\Support\Collection */
    protected $sections;

    public function __construct(Collector $collector, Haydar $haydar, Dispatcher $events)
    {
        $this->collector = $collector;
        $this->haydar = $haydar;
        $this->events = $events;
    }

    public function slug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function add($section)
    {
        $this->sections->put('aaaa', collect([$section]));

        return $this;
    }

    protected function make()
    {
        $this->sections = $this->collector->collect($this->slug);

        return $this;
    }

    protected function build()
    {
        $this->make();

        $event = 'navigation.'.$this->slug.':building';
        $this->events->dispatch($event, $this);

        $sections = $this->sections->map(Closure::fromCallable([$this, 'buildSections']))
                                   ->values()
                                   ->flatten(1);

        $this->navigation = [
            'slug'     => $this->slug,
            'sections' => $sections->sortByDesc('priority')->values()->all(),
        ];
    }

    protected function buildSections($sections)
    {
        return collect($sections)->map(function ($section) {
            if (is_array($section)) {
                $section = Section::make($section);
            }

            return $section->parent($this->slug)->build();
        })->filter()->all();
    }

    public function get()
    {
        $this->build();

        return $this->navigation;
    }
}