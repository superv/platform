<?php

namespace SuperV\Platform\Packs\Routing;

use Illuminate\Routing\Router;
use Platform;

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

    public function load(array $routes, $port = null)
    {
        foreach ($routes as $uri => $action) {

            if (! is_array($action)) {
                $action = ['uses' => $action];
            }
            if (str_contains($uri, '@')) {
                list($verb, $uri) = explode('@', $uri);
            }
            if ($port) {
                array_set($action, 'port', $port);
                $portConfig = Platform::config("ports.{$port}");
                array_set($action, 'domain', $portConfig['hostname']);

                if ($prefix = array_get($portConfig, 'prefix')) {
                    array_set($action, 'prefix', $prefix);
                }
            }
            $this->router->{$verb ?? 'get'}($uri, $action);
        }
    }
}