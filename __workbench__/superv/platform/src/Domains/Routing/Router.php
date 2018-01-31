<?php

namespace SuperV\Platform\Domains\Routing;

class Router
{
    /**
     * @var \SuperV\Platform\Domains\Routing\RouteLoader
     */
    protected $loader;

    public function __construct(RouteLoader $loader)
    {
        $this->loader = $loader;
    }

    public function forPorts($path) {
        if ($folders = glob(base_path("{$path}/*"), GLOB_ONLYDIR)) {

            foreach ($folders as $folder) {
                $port = pathinfo($folder, PATHINFO_BASENAME);

                $files = glob("{$folder}/*.php");

                foreach($files as $file) {
                    $routes = (array)require $file;
                    $this->loader->load($routes, $port);
                }
            }
        }
    }

    public function loadFromFile($file)
    {
        $routes = require base_path($file);
        $this->loader->load($routes);
    }
}