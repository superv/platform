<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Droplet\Droplet;

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
//                array_set($route, 'as', $as);
                array_set($route, 'superv::port', "superv.ports.{$port}"); // TODO.ali: generic namespace
            }
        }

        // Add droplet signature
        array_set($route, 'superv::droplet', $this->droplet->getSlug());

        $verb = array_pull($route, 'verb', 'any');
        $middleware = array_pull($route, 'middleware', []);
        $constraints = array_pull($route, 'constraints', []);

        if (is_string($route['uses']) && !str_contains($route['uses'], '@')) {
            $router->resource($this->uri, $route['uses']);
        } else {

            $route = $router->{$verb}($this->uri, $route)->where($constraints);

            if ($middleware) {
                call_user_func_array([$route, 'middleware'], (array)$middleware);
            }
        }
    }
}