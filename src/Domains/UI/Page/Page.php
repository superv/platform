<?php namespace SuperV\Platform\Domains\UI\Page;

use SuperV\Platform\Domains\Droplet\Droplet;

class Page
{
    protected $manifest;

    /** @var  Droplet */
    protected $droplet;

    protected $page;

    // buttons, table, form, url,
    protected $route;

    protected $url;

    protected $handler;

    protected $title;

    protected $public = false;

    protected $navigation = false;


    /**
     * @param mixed $page
     *
     * @return Page
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param mixed $route
     *
     * @return Page
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @param mixed $url
     *
     * @return Page
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param mixed $handler
     *
     * @return Page
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @param mixed $manifest
     *
     * @return Page
     */
    public function setManifest($manifest)
    {
        $this->manifest = $manifest;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
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
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param Droplet $droplet
     *
     * @return Page
     */
    public function setDroplet(Droplet $droplet): Page
    {
        $this->droplet = $droplet;

        return $this;
}

    /**
     * @return Droplet
     */
    public function getDroplet(): Droplet
    {
        return $this->droplet;
    }

    /**
     * @return bool
     */
    public function isNavigation(): bool
    {
        return $this->navigation;
    }

    public function setNavigation(bool $navigation)
    {
        $this->navigation = $navigation;

        return $this;
    }

    public function getHref()
    {
        return $this->url;
    }
}