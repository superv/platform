<?php

namespace SuperV\Platform\Domains\Manifest;

use SuperV\Platform\Support\Collection;

class Manifest
{
    protected $droplet;

    protected $pages;

    protected $icon;

    /**
     * Raw manifest data
     *
     * @var  array
     */
    protected $data;

    protected $port;

    protected $routeKeyName = 'id';

    protected $model;

    public function __construct(Collection $pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return Collection
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage($route, $page)
    {
        $this->pages->put($route, $page);

        return $this;
    }

    public function setPages($pages): Manifest
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @param mixed $icon
     *
     * @return Manifest
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
     * @param null $key
     *
     * @return array
     */
    public function getData($key = null)
    {
        if ($key) {
            return array_get($this->data, $key);
        }
        return $this->data;
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
     * @return Manifest
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return $this->routeKeyName;
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
     * @return Manifest
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
}

    /**
     * @param mixed $droplet
     *
     * @return Manifest
     */
    public function setDroplet($droplet)
    {
        $this->droplet = $droplet;

        return $this;
}

    /**
     * @return mixed
     */
    public function getDroplet()
    {
        return $this->droplet;
    }


}
