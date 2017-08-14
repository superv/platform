<?php namespace SuperV\Platform\Domains\UI\Menu;

use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Support\Collection;

class Section
{
    protected $title;

    protected $pages;

    protected $roles = [];

    protected $sortOrder = 0;

    public function __construct(Collection $pages)
    {
        $this->pages = $pages;
    }

    public function addPage(Page $page)
    {
        $this->pages->push($page);
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
}