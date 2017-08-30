<?php

namespace SuperV\Platform\Domains\UI\Navigation;

class Navigation
{
    protected $sections = [];

    protected $activeModule;

    public function addSection(Section $section)
    {
        $this->sections[] = $section;
    }

    /**
     * @return array
     */
    public function getSections(): array
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
