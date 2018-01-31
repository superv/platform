<?php

namespace SuperV\Platform\Domains\Routing;

class Router
{
    /**
     * @var \SuperV\Platform\Domains\Routing\RouteLoader
     */
    protected $loader;

    public function __construct(RouteLoader $loader)
    {
        $this->loader = $loader;
    }

    public function loadFromFile($file)
    {
        $routes = require base_path($file);
        $this->loader->load($routes);
    }
}