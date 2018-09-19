<?php

namespace SuperV\Platform\Domains\Navigation;

use SuperV\Modules\Guard\Domains\Guard\Guardable;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Support\Concerns\Hydratable;

class Section implements SectionBag, Guardable
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

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $events;

    protected $guardKey;

    protected $priority = 10;

    public function __construct(Dispatcher $events)
    {
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

    public function building()
    {
        $this->dispatchEvent();

        return $this;
    }

    public function build()
    {
        return array_filter([
            'title'    => $this->getTitle(),
            'slug'     => $this->getSlug(),
            'icon'     => $this->icon,
            'url'      => $this->url,
            'priority' => $this->priority,
            'sections' => $this->buildSections($this->sections),
        ]);
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
                    $section->parent($this);

                    $section->building();

                    return $section;
                })
            ->guard()
            ->filter()
            ->map(function (Section $section) {
                return $section->build();
            })
            ->all();
    }

    protected function getTitle(): string
    {
        return $this->title ?: ucwords(str_replace(['_', '.'], ' ', $this->slug));
    }

    protected function dispatchEvent()
    {
        $event = 'navigation.'.$this->namespace().':building';
        $this->events->dispatch($event, $this);
    }

    /**
     * @param string $title
     * @return Section
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function guardKey(): ?string
    {
        if ($this->guardKey) {
            return $this->guardKey;
        }

        if ( $this->parent instanceof Section) {
            $guardKey = $this->parent->guardKey() . '.'.$this->getSlug();
        }

        return $guardKey ?? null;
    }

    public function setGuardKey($key)
    {
        $this->guardKey = $key;

        return $this;
    }

    protected function getSlug()
    {
        if (! $this->slug) {
            $this->slug = str_slug(strtolower($this->title), '_');
        }

        return $this->slug;
    }

    public function add($section)
    {
        $this->sections[] = $section->parent($this);

        return $this;
    }

    public function sections(array $sections)
    {
        $this->sections = $sections;

        return $this;
    }

    public function icon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }

    public function url(string $url)
    {
        $this->url = $url;

        return $this;
    }

    public function parent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function namespace()
    {
        $parent = $this->parent instanceof Section ? $this->parent->namespace() : $this->parent;

        return ($parent ? $parent.'.' : '').$this->getSlug();
    }

    public function priority(int $priority)
    {
        $this->priority = $priority;

        return $this;
    }
}