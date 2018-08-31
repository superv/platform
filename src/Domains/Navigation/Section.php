<?php

namespace SuperV\Platform\Domains\Navigation;

use Closure;
use SuperV\Platform\Domains\Authorization\Haydar;
use SuperV\Platform\Support\Concerns\Hydratable;

class Section
{
    use Hydratable;

    /**
     * @var string
     */
    protected $slug;

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

    public function __construct(Haydar $haydar, \SuperV\Platform\Contracts\Dispatcher $events)
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
            $section->slug = $slug;
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

        $this->events->dispatch('nav.acp_main.'.$this->slug.':building', $this);

        return array_filter([
            'title'    => $this->title ?: ucwords(str_replace('_', ' ', $this->slug)),
            'icon'     => $this->icon,
            'url'      => $this->url,
            'sections' => collect($this->sections)
                ->map(Closure::fromCallable([$this, 'buildOne']))
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

        return $section->build();
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
     * @return Section
     */
    public function ability($ability)
    {
        $this->ability = $ability;

        return $this;
    }
}