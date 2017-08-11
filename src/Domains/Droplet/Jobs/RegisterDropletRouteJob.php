<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Http\Middleware\MiddlewareCollection;

class RegisterDropletRouteJob
{
    private $uri;

    private $route;

    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Droplet $droplet, $uri, array $route)
    {
        $this->uri = $uri;
        $this->route = $route;
        $this->droplet = $droplet;
    }

    public function handle(Router $router)
    {
        $route = $this->route;

        if (array_has($route, 'as')) {
            if (str_contains($route['as'], '@')) {
                list($port, $as) = explode('@', $route['as']);
                array_set($route, 'superv::port', "superv.ports.{$port}"); // TODO.ali: generic namespace

                if ($middlewares = superv(MiddlewareCollection::class)->get("superv.ports.{$port}")) {
                    array_set($route, 'middleware', $middlewares); // TODO.ali: merge instead of set
                }
            }
        }

        if (is_callable($route['uses']) && $route['uses'] instanceof \Closure) {
//            $route['uses']->bindTo($router);
        }

        // Add droplet signature
        array_set($route, 'superv::droplet', $this->droplet->getSlug());

        /** @var Route $routex */
        $routex = $router->{array_pull($route, 'verb', 'any')}($this->uri, $route);

        $routex->middleware(array_pull($route, 'middleware', []));
        $routex->where(array_pull($route, 'constraints', []));
//        $routex->setAction(
//            [
//                'as' => array_get($route,'as'),
//                'uses' => $route['uses'],
//                'superv::droplet' => $this->droplet->getSlug()
//            ]
//        );

//        if (is_string($route['uses']) && !str_contains($route['uses'], '@')) {
//            $router->resource($this->uri, $route['uses']);
//        } else {
//
//        }
    }
}