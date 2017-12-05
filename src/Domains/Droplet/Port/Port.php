<?php

namespace SuperV\Platform\Domains\Droplet\Port;

use SuperV\Platform\Domains\Droplet\Droplet;

class Port extends Droplet
{
    protected $theme;

    protected $hostname;

    protected $prefix;

    protected $type = 'port';

    protected $routes = [];

    protected $middlewares = [];

    public function addRoute($uri, $data)
    {
        if ($this->prefix) {
            array_set($data, 'prefix', $this->prefix);
        }
        array_set($this->routes, $uri, $data);
    }

    /**
     *  Setters & Getters
     */

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return mixed
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param mixed $theme
     *
     * @return Port
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param mixed $hostname
     *
     * @return Port
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param array $middlewares
     *
     * @return Port
     */
    public function setMiddlewares(array $middlewares): Port
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @param mixed $prefix
     *
     * @return Port
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
}

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}
