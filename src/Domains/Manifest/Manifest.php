<?php

namespace SuperV\Platform\Domains\Manifest;

use SuperV\Platform\Support\Collection;

abstract class Manifest
{
    protected $pages;

    protected $icon;

    public function __construct(Collection $pages)
    {
        $this->pages = $pages;
    }

    public function pages()
    {
        return $this->pages;
    }

    public function setPages($pages): Manifest
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @param mixed $icon
     *
     * @return Manifest
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
