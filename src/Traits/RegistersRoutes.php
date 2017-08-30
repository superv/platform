<?php

namespace SuperV\Platform\Traits;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use SuperV\Platform\Http\Middleware\MiddlewareCollection;

trait RegistersRoutes
{
    protected function registerRoutes(array $routes, \Closure $callable = null)
    {
        /** @var Router $router */
        $router = superv('router');
        foreach ($routes as $uri => $data) {
            $data = !is_array($data) ? ['uses' => $data] : $data;

            if (array_has($data, 'as')) {
                if (str_contains($data['as'], '@')) {
                    list($port, $as) = explode('@', $data['as']);
                    array_set($data, 'superv::port', "superv.ports.{$port}"); // TODO.ali: generic namespace

                    if ($middlewares = superv(MiddlewareCollection::class)->get("superv.ports.{$port}")) {
                        array_set($data, 'middleware', $middlewares); // TODO.ali: merge instead of set
                    }
                }
            }

            /** @var Route $route */
            $route = $router->{array_pull($data, 'verb', 'any')}($uri, $data);

            $route->middleware(array_pull($data, 'middleware', []));
            $route->where(array_pull($data, 'constraints', []));

            if ($callable) {
                call_user_func($callable, $route);
            }
        }
    }
}
