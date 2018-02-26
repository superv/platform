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
        if (! is_array($action)) {
            $action = ['uses' => $action];
        }
        if (str_contains($uri, '@')) {
            list($verb, $uri) = explode('@', $uri);
        }
        if ($this->port) {
            array_set($action, 'port', $this->port->slug());
            array_set($action, 'domain', $this->port->hostname());

            if ($prefix = $this->port->prefix()) {
                array_set($action, 'prefix', $prefix);
            }

            if ($middlewares = $this->port->middlewares()) {
                array_set($action, 'middleware', array_merge($middlewares, $action['middleware'] ?? []));
            }
        }

        return $this->router->{$verb ?? 'get'}($uri, $action);
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