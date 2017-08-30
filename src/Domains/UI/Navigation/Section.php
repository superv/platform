<?php

namespace SuperV\Platform\Domains\UI\Navigation;

use SuperV\Platform\Support\Collection;
use SuperV\Platform\Domains\UI\Page\Page;

class Section
{
    protected $title;

    protected $pages;

    protected $icon;

    protected $roles = [];

    protected $sortOrder = 0;

    protected $module;

    /**
     * @var Navigation
     */
    private $navigation;

    public function __construct(Navigation $navigation)
    {
        $this->navigation = $navigation;
    }

    public function addPage(Page $page)
    {
        $this->pages[] = $page;
    }

    /**
     * @return Collection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     *
     * @return Section
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param int $sortOrder
     *
     * @return Section
     */
    public function setSortOrder(int $sortOrder): Section
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @param mixed $icon
     *
     * @return Section
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

    /**
     * @param mixed $module
     *
     * @return Section
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    public function isActive()
    {
        return $this->navigation->getActiveModule() == $this->module;
    }
}
