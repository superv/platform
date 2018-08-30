<?php

namespace SuperV\Platform\Domains\Navigation;

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

    public function __construct(string $slug = null, string $title = null)
    {
        $this->slug = $slug;
        $this->title = $title;
    }

    public static function make($slug)
    {
        return new static($slug);
    }

    public function build()
    {
        return array_filter([
            'title'    => $this->title ?: ucwords(str_replace('_', ' ', $this->slug)),
            'icon'     => $this->icon,
            'url'      => $this->url,
            'sections' => collect($this->sections)->map(function ($item) {
                if ($item instanceof Section) {
                    return $item->build();
                }

                return (new Section())->hydrate($item)->build();
            })->all(),
        ]);
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
}