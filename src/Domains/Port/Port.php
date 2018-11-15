<?php

namespace SuperV\Platform\Domains\Port;

use SuperV\Platform\Support\Concerns\Hydratable;

class Port
{
    use Hydratable;

    protected $slug;

    protected $hostname;

    protected $prefix = null;

    protected $theme = null;

    protected $roles = [];

    protected $model;

    protected $middlewares;

    protected $guard;

    protected $composers;

    protected $navigationSlug;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

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
     * @return null
     */
    public function prefix()
    {
        return $this->prefix;
    }

    public function root()
    {
        return $this->hostname().($this->prefix ? '/'.$this->prefix : '');
    }

    /**
     * @return null
     */
    public function theme()
    {
        return $this->theme;
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
     * @return mixed
     */
    public function middlewares()
    {
        return $this->middlewares;
    }

    /**
     * @return string
     */
    public function guard()
    {
        return $this->guard;
    }

    /**
     * @return mixed
     */
    public function getComposers()
    {
        return $this->composers;
    }

    /**
     * @return mixed
     */
    public function getNavigationSlug()
    {
        return $this->navigationSlug;
    }
}