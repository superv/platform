<?php

namespace SuperV\Platform\Domains\Navigation;

use Closure;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Authorization\Haydar;
use SuperV\Platform\Support\Concerns\Hydratable;

class Section implements SectionBag
{
    use Hydratable;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $parent;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $sections;

    /** @var Haydar */
    protected $haydar;

    protected $ability;

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $events;

    protected $priority = 100;

    public function __construct(Haydar $haydar, Dispatcher $events)
    {
        $this->haydar = $haydar;
        $this->events = $events;
    }

    public static function make($slug)
    {
        $section = app(static::class);

        if (is_array($slug)) {
            $section->hydrate($slug);
        } else {
            $section->slug = strtolower($slug);
        }

        return $section;
    }

    protected function guard()
    {
        return $this->haydar->can($this->ability);
    }

    public function build()
    {
        if (! $this->guard()) {
            return [];
        }

        if (! $this->slug) {
            $this->slug = str_slug(strtolower($this->title));
        }

        $event = 'navigation.'.$this->namespace().':building';
        $this->events->dispatch($event, $this);

        return array_filter([
            'title'    => $this->title ?: ucwords(str_replace('_', ' ', $this->slug)),
            'slug'     => $this->slug,
            'icon'     => $this->icon,
            'url'      => $this->url,
            'priority' => $this->priority,
            'sections' => collect($this->sections)
                ->map(Closure::fromCallable([$this, 'buildOne']))
                ->sortByDesc('priority')
                ->values()
                ->all(),
        ]);
    }

    public function add($section)
    {
        $this->sections[] = $section;

        return $this;
    }

    protected function buildOne($section)
    {
        if (is_array($section)) {
            $section = static::make($section);
        }

        return $section->parent($this)->build();
    }

    public function sections(array $sections)
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * @param string $icon
     * @return Section
     */
    public function icon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param string $url
     * @return Section
     */
    public function url(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param $ability
     * @return Section
     */
    public function ability($ability)
    {
        $this->ability = $ability;

        return $this;
    }

    /**
     * @param string $parent
     * @return Section
     */
    public function parent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function namespace()
    {
        return ($this->parent instanceof Section ? $this->parent->namespace() : $this->parent).'.'.$this->slug;
    }

    /**
     * @param int $priority
     * @return Section
     */
    public function priority(int $priority)
    {
        $this->priority = $priority;

        return $this;
    }
}