<?php

namespace SuperV\Platform\Domains\Routing;

use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Port\Port;

class RouteRegistrar
{
    /**
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /** @var \SuperV\Platform\Domains\Port\Port */
    protected $port;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register multiple routes
     *
     * @param array $routes
     */
    public function register(array $routes)
    {
        foreach ($routes as $uri => $action) {
            $this->registerRoute($uri, $action);
        }
    }

    /**
     * Register a route
     *
     * @param $uri
     * @param $action
     * @return \Illuminate\Routing\Route
     */
    public function registerRoute($uri, $action)
    {
        return Action::make($uri, $action)
                     ->port($this->port)
                     ->build()
                     ->register($this->router);
    }

    /**
     * @param \SuperV\Platform\Domains\Port\Port|string $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = is_object($port) ? $port : Port::fromSlug($port);

        return $this;
    }
}