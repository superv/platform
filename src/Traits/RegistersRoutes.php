<?php namespace SuperV\Platform\Traits;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use SuperV\Platform\Http\Middleware\MiddlewareCollection;

trait RegistersRoutes
{
    protected function registerRoutes(array $routes, \Closure $callable = null)
    {
        /** @var Router $router */
        $router = superv('router');
        foreach ($routes as $uri => $route) {
            $route = !is_array($route) ? ['uses' => $route] : $route;

            if (array_has($route, 'as')) {
                if (str_contains($route['as'], '@')) {
                    list($port, $as) = explode('@', $route['as']);
                    array_set($route, 'superv::port', "superv.ports.{$port}"); // TODO.ali: generic namespace

                    if ($middlewares = superv(MiddlewareCollection::class)->get("superv.ports.{$port}")) {
                        array_set($route, 'middleware', $middlewares); // TODO.ali: merge instead of set
                    }
                }
            }

            if ($callable) {
                call_user_func($callable, $route);
            }
            // Add droplet signature
//            array_set($route, 'superv::droplet', $this->droplet->getSlug());

            /** @var Route $routeObject */
            $routeObject = $router->{array_pull($route, 'verb', 'any')}($uri, $route);

            $routeObject->middleware(array_pull($route, 'middleware', []));
            $routeObject->where(array_pull($route, 'constraints', []));
        }
    }
}