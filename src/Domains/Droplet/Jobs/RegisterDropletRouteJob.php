<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

use Illuminate\Routing\Router;

class RegisterDropletRouteJob
{
    private $uri;

    private $route;

    public function __construct($uri, array $route)
    {
        $this->uri = $uri;
        $this->route = $route;
    }

    public function handle(Router $router)
    {
        $verb = array_pull($this->route, 'verb', 'any');
        $middleware = array_pull($this->route, 'middleware', []);
        $constraints = array_pull($this->route, 'constraints', []);

        if (is_string($this->route['uses']) && !str_contains($this->route['uses'], '@')) {
            $router->resource($this->uri, $this->route['uses']);
        } else {

            $this->route = $router->{$verb}($this->uri, $this->route)->where($constraints);

            if ($middleware) {
                call_user_func_array([$this->route, 'middleware'], (array)$middleware);
            }
        }
    }
}