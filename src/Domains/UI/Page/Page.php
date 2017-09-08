<?php

namespace SuperV\Platform\Domains\UI\Page;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\UI\Page\Features\RenderPage;

class Page
{
    use ServesFeaturesTrait;

    protected $manifest;

    /** @var Droplet */
    protected $droplet;

    protected $verb;

    protected $port;

    protected $route;

    protected $url;

    protected $handler;

    protected $title;

    protected $buttons = [];

    protected $public = false;

    protected $navigation = false;

    protected $model;

    protected $entry;

    protected $entryRouteKeyName = 'id';

    protected $icon;

    protected $rendered;

    protected $content;

    public function render()
    {
        return $this->content = $this->dispatch(new RenderPage($this));
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     *
     * @return Page
     */
    public function setPublic(bool $public): Page
    {
        $this->public = $public;

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
     * @param mixed $icon
     *
     * @return Page
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

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
     * @return Page
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    public function makeRoute()
    {
        return route($this->route, ($this->entry && $this->entry->exists) ? ['entry' => $this->entry] : []);
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
     * @return Page
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
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
     * @return Droplet
     */
    public function getDroplet(): Droplet
    {
        return $this->droplet;
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

    /**
     * @return array
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @param array $buttons
     *
     * @return Page
     */
    public function setButtons(array $buttons): Page
    {
        $this->buttons = $buttons;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     *
     * @return Page
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /** @return $this->model */
    public function newEntry()
    {
        return app($this->model);
    }

    /**
     * @return mixed
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param mixed $entry
     *
     * @return Page
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntryRouteKeyName()
    {
        return $this->entryRouteKeyName;
    }

    /**
     * @param mixed $entryRouteKeyName
     *
     * @return Page
     */
    public function setEntryRouteKeyName($entryRouteKeyName)
    {
        $this->entryRouteKeyName = $entryRouteKeyName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getManifest()
    {
        return $this->manifest;
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
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * @param mixed $verb
     *
     * @return Page
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     *
     * @return Page
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     *
     * @return Page
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
