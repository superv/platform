<?php

namespace SuperV\Platform\Domains\Port;

use SuperV\Platform\Support\Inflator;

class Port
{
    protected $slug;

    protected $hostname;

    protected $prefix = null;

    protected $theme = null;

    protected $roles = [];

    protected $model;

    protected $middlewares;

    protected $guard;

    /**
     * @return mixed
     */
    public function slug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function hostname()
    {
        return $this->hostname;
    }

    /**
     * @param mixed $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return null
     */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * @param null $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return null
     */
    public function theme()
    {
        return $this->theme;
    }

    /**
     * @param null $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public static function fromSlug($slug)
    {
        $config = \Platform::config('ports.'.$slug);

        if (!$config) {
            throw new \Exception("Port config not found: [{$slug}]");
        }

        /** @var self $port */
        $port = Inflator::inflate(app(Port::class), $config);

        $port->setSlug($slug);

        return $port;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function roles()
    {
        return $this->roles;
    }

    /**
     * @return mixed
     */
    public function model()
    {
        return $this->model;
    }

    public function resolveModel()
    {
        $class = $this->model();

        return new $class;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function middlewares()
    {
        return $this->middlewares;
    }

    /**
     * @param mixed $middlewares
     */
    public function setMiddlewares($middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * @return string
     */
    public function guard()
    {
        return $this->guard;
    }

    /**
     * @param string $guard
     */
    public function setGuard($guard)
    {
        $this->guard = $guard;
    }
}