<?php

namespace SuperV\Platform\Domains\Droplet\Port;

class Routes
{
    protected $routes = [];

    public function put($port, $uri, $data)
    {
        $routes = array_get($this->routes, $port, []);

        $routes[$uri] = $data;

        array_set($this->routes, $port, $routes);
    }

    public function byPort($port)
    {
        return array_get($this->routes, $port, []);
    }

    public function disperse(array $routes, \Closure $callable = null)
    {
        foreach ($routes as $uri => $data) {
            $data = ! is_array($data) ? ['uses' => $data] : $data;

            if ($callable) {
                $data = call_user_func($callable, $data);
            }
            /**
             * All routes registered through platform
             * should have a port defined
             */
            if (! $port = array_pull($data, 'superv::port')) {
                if (! $port = array_pull($data, 'port')) {
                    throw new \LogicException("URI {$uri} does not have a port");
                }
            }

            if (! str_is('*.*.*', $port)) {
                $port = "superv.ports.{$port}";
            }

            /** @var Port $port */
            if (! $port = superv('ports')->bySlug($port)) {
                throw new \LogicException("Port {$port} not found for route: {$uri}");
                continue;
            }

            if ($port->isActive()) {
                $port->registerRoutes([$uri => $data]);
            } else {
                $this->put($port->getSlug(), $uri, $data);
            }
        }
    }
}