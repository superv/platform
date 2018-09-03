<?php

namespace SuperV\Platform\Domains\Routing;

use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Port\Port;

class RouteRegistrar
{
    /**
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * @var \SuperV\Platform\Domains\Port\Port
     */
    protected $port;

    /**
     * Registered Routes
     *
     * @var \Illuminate\Support\Collection
     */
    protected $routes;

    /**
     * Flag for registering routes for every port available
     *
     * @var boolean
     */
    protected $globally;

    public function __construct(Router $router, Collection $routes)
    {
        $this->router = $router;
        $this->routes = $routes;
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
     * @return \Illuminate\Support\Collection
     */
    public function registerRoute($uri, $action)
    {
        /** Register this route for every port available */
        if ($this->globally) {
            $ports = Port::all();
        } else {
            $ports = collect([$this->port]);
        }

        $ports->map(function ($port) use ($action, $uri) {
            $this->routes->push(
                Action::make($uri, $action)
                      ->port($port)
                      ->build()
                      ->register($this->router)
            );
        });

        return $this->routes;
    }

    /**
     * @param \SuperV\Platform\Domains\Port\Port|string $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @param $global
     * @return RouteRegistrar
     */
    public function globally($global = true)
    {
        $this->globally = $global;

        return $this;
    }
}