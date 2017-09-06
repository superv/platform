<?php

namespace SuperV\Platform\Traits;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Droplet\Port\Port;

trait RegistersRoutes
{
    protected function registerRoutes(array $routes, \Closure $callable = null)
    {
        /** @var Router $router */
        $router = app('router');
        foreach ($routes as $uri => $data) {

            $data = ! is_array($data) ? ['uses' => $data] : $data;

            $middlewares = array_pull($data, 'middleware', []);

            /** @var Route $route */
            $route = $router->{array_pull($data, 'verb', 'any')}($uri, $data);
            $route->where(array_pull($data, 'constraints', []));


            if ($callable) {
                call_user_func($callable, $route);
            }

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

            $route->domain($port->getHostname());

            $middlewares = array_merge($middlewares, $port->getMiddlewares());

            $port->addRoute($uri, $route);

            $route->middleware($middlewares);
        }
    }
}
