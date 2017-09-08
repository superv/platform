<?php

namespace SuperV\Platform\Domains\UI\Navigation;

use SuperV\Platform\Domains\Droplet\Droplet;

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
     * @return mixed
     */
    public function getActiveModule()
    {
        return $this->activeModule;
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

    public function addDropletSection(Droplet $droplet)
    {
        $section = (new Section($this))->setTitle($droplet->getTitle())
                                       ->setIcon($droplet->getIcon())
                                       ->setModule($droplet)
                                       ->setSortOrder($droplet->getSortOrder());

        $this->addSection($section);

        return $section;
    }
}
