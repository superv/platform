<?php namespace SuperV\Platform\Domains\Manifest;

use SuperV\Platform\Support\Collection;

abstract class Manifest
{
    protected $pages;

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


}