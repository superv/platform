<?php

namespace SuperV\Platform\Domains\Port;

use SuperV\Platform\Support\Concerns\Hydratable;

class Port
{
    use Hydratable;

    protected $slug;

    protected $hostname;

    protected $secure = false;

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

    public function slug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function hostname()
    {
        return $this->hostname ?? config('superv.hostname');
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function root()
    {
        return $this->hostname().($this->prefix ? '/'.$this->prefix : '');
    }

    public function theme()
    {
        return $this->theme;
    }

    public function roles()
    {
        return $this->roles;
    }

    public function model()
    {
        return $this->model;
    }

    public function resolveModel()
    {
        $class = $this->model();

        return new $class;
    }

    public function middlewares()
    {
        return $this->middlewares;
    }

    public function guard()
    {
        return $this->guard;
    }

    public function url()
    {
        return ($this->secure ? 'https://' : 'http://').$this->hostname().($this->prefix() ? '/'.$this->prefix() : '');
    }

    public function getComposers()
    {
        return $this->composers;
    }

    public function getNavigationSlug()
    {
        return $this->navigationSlug;
    }
}