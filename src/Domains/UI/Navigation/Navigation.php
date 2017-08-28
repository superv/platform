<?php namespace SuperV\Platform\Domains\UI\Navigation;

use SuperV\Platform\Support\Collection;

class Navigation
{
    protected $sections;

    protected $activeModule;

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

    /**
     * @param mixed $activeModule
     *
     * @return Navigation
     */
    public function setActiveModule($activeModule)
    {
        $this->activeModule = $activeModule;

        return $this;
}

    /**
     * @return mixed
     */
    public function getActiveModule()
    {
        return $this->activeModule;
    }
}