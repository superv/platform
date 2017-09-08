<?php

namespace SuperV\Platform\Domains\UI\Page;

class Tab
{
    private $tab;

    private $title;

    private $url;

    private $route;

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
     * @return Tab
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     *
     * @return Tab
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     *
     * @return Tab
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getTab()
    {
        return $this->tab;
    }

    /**
     * @param mixed $tab
     *
     * @return Tab
     */
    public function setTab($tab)
    {
        $this->tab = $tab;

        return $this;
    }


}