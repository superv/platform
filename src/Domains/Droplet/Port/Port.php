<?php

namespace SuperV\Platform\Domains\Droplet\Port;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Droplet\Droplet;

class Port extends Droplet
{
    protected $theme;

    protected $hostname;

    protected $prefix;

    protected $type = 'port';

    protected $routes = [];

    protected $middlewares = [];

    protected $active = false;

    public function registerRoutes($routes = null)
    {
        /** @var Router $router */
        $router = app('router');

//        if (! empty($routes)) {
//            \Log::info('Registering routes', ['routes' => array_keys($routes)]);
//        }

        foreach ($routes as $uri => $data) {
            $middlewares = array_pull($data, 'middleware', []);
            if (str_contains($uri, '@')) {
                list($verb, $uri) = explode('@', $uri);
            } else {
                $verb = array_pull($data, 'verb', 'any');
            }

            /** @var Route $route */
            $route = $router->{$verb}($uri, $data);
            $route->where(array_pull($data, 'constraints', []));
            if ($this->prefix) {
                $route->prefix($this->prefix);
            }
            $route->domain($this->getHostname());
            $route->middleware(array_merge((array)$middlewares, $this->getMiddlewares()));
        }
    }

    public function addRoute($uri, $data)
    {
        if ($this->prefix) {
            array_set($data, 'prefix', $this->prefix);
        }
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

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }
}
