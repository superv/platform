<?php

namespace SuperV\Platform\Domains\Navigation;

use Closure;
use SuperV\Platform\Domains\Auth\Access\Guard\HasGuardableItems;

class Navigation implements SectionBag, HasGuardableItems
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
     * @var array
     */
    protected $navigation;

    /**
     * @var \SuperV\Platform\Domains\Navigation\Collector
     */
    protected $collector;

    /** @var \Illuminate\Support\Collection */
    protected $sections;

    public function __construct(Collector $collector)
    {
        $this->collector = $collector;
    }

    public function slug($slug)
    {
        $this->slug = $slug;

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
        app('events')->dispatch($event, $this);

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
                    } elseif (! $section instanceof Section) {
                        return null; // invalid section
                    }
                    $section->parent($this->slug);

                    $section->building();

                    return $section;
                })
            ->filter()
            ->guard()
            ->map(function (Section $section) {
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

    public function add($section)
    {
    }
}