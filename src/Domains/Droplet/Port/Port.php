<?php

namespace SuperV\Platform\Domains\Droplet\Port;

use SuperV\Platform\Domains\Droplet\Droplet;

class Port extends Droplet
{
    protected $hostname;

    protected $type = 'port';

    protected $routes = [];

    protected $middlewares = [];

    public function getHostname()
    {
        return $this->hostname;
    }

    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function addRoute($uri, $data)
    {
        array_set($this->routes, $uri, $data);
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
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
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
