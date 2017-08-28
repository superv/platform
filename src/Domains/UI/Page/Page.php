<?php namespace SuperV\Platform\Domains\UI\Page;

use SuperV\Platform\Domains\Droplet\Droplet;

class Page
{
    protected $manifest;

    /** @var  Droplet */
    protected $droplet;

    protected $verb;

    // buttons, table, form, url,
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
    public function getRoute()
    {
        return $this->route;
    }

    public function makeRoute()
    {
        return route($this->route, ($this->entry && $this->entry->exists) ? ['entry' => $this->entry]:  []);
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
     * @return array
     */
    public function getButtons(): array
    {
        return $this->buttons;
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

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /** @return $this->model */
    public function newEntry()
    {
        return superv($this->model);
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
    public function getEntry()
    {
        return $this->entry;
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
    public function getVerb()
    {
        return $this->verb;
    }
}