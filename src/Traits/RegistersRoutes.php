<?php

namespace SuperV\Platform\Traits;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Droplet\Port\Port;

trait RegistersRoutes
{
    protected function disperseRoutes(array $routes, \Closure $callable = null)
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
                throw new \LogicException("Port {$port} not found");
            }

            $port->addRoute($uri, $data);
        }
    }

    protected function registerRoutes(Port $port)
    {
        /** @var Router $router */
        $router = app('router');
        foreach ($port->getRoutes() as $uri => $data) {
            $middlewares = array_pull($data, 'middleware', []);

            $verb = array_pull($data, 'verb', 'any');
            /** @var Route $route */
            $route = $router->{$verb}($uri, $data);
            $route->where(array_pull($data, 'constraints', []));
            $route->domain($port->getHostname());
            $route->middleware(array_merge($middlewares, $port->getMiddlewares()));
        }
    }
}
