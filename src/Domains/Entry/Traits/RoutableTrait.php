<?php

namespace SuperV\Platform\Domains\Entry\Traits;

use SuperV\Platform\Domains\Entry\EntryRouter;

trait RoutableTrait
{
    protected $cache;

    public function getRouterName()
    {
        $router = substr(get_class($this), 0, -5).'Router';

        return class_exists($router) ? $router : EntryRouter::class;
    }

    public function route($route, array $parameters = [])
    {
        $router = $this->getRouter();

        return $router->make($route, $parameters);
    }

    /**
     * Return a new router instance.
     *
     * @return EntryRouter
     */
    public function newRouter()
    {
        return superv($this->getRouterName(), ['entry' => $this]);
    }

    public function getRouter()
    {
        if (isset($this->cache['router'])) {
            return $this->cache['router'];
        }

        return $this->cache['router'] = $this->newRouter();
    }
}
