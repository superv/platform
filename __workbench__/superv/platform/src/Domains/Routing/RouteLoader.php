<?php

namespace SuperV\Platform\Domains\Routing;

use Illuminate\Routing\Router;

class RouteLoader
{
    /**
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function load(array $routes)
    {
        foreach ($routes as $uri => $data) {

            if (str_contains($uri, '@')) {
                list($verb, $uri) = explode('@', $uri);
            }
            $this->router->{$verb ?? 'get'}($uri, $data);
        }
    }
}