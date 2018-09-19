<?php

namespace SuperV\Platform\Domains\Navigation;

use Closure;
use Illuminate\Events\Dispatcher;
use SuperV\Modules\Guard\Domains\Guard\HasGuardableItems;

class Navigation implements SectionBag, HasGuardableItems
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
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /** @var \Illuminate\Support\Collection */
    protected $sections;

    public function __construct(Collector $collector, Dispatcher $events)
    {
        $this->collector = $collector;
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

        $sections = $this->sections
            ->map(Closure::fromCallable([$this, 'buildSections']))
            ->values()
            ->flatten(1);

        $this->navigation = [
            'slug'     => $this->slug,
            'sections' => $sections->sortByDesc('priority')->values(),
        ];
    }

    protected function buildSections($sections)
    {
        return collect($sections)
            ->map(
                function ($section) {
                    if (is_array($section)) {
                        $section = Section::make($section);
                    } elseif ($section instanceof HasSection) {
                        $section = $section::getSection();
                    }
                    $section->parent($this->slug);

                    return $section;
                })
            ->guard()
            ->filter()
            ->map(function(Section $section) {
                return $section->build();
            })
            ->all();
    }

    public function get()
    {
        $this->build();

        return $this->navigation;
    }

    public function getGuardableItems()
    {
        return $this->navigation['sections'];
    }

    public function setGuardableItems($items)
    {
        $this->navigation['sections'] = $items;
    }
}