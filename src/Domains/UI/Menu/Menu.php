<?php namespace SuperV\Platform\Domains\UI\Menu;

use SuperV\Platform\Support\Collection;

class Menu
{
    protected $sections;

    public function __construct(Collection $sections)
    {
        $this->sections = $sections;
    }

    public function addSection(Section $section)
    {
        $this->sections->push($section);
    }

    /**
     * @return Collection
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }
}