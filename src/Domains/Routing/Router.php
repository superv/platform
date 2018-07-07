<?php

namespace SuperV\Platform\Domains\Routing;

class Router
{
    /**
     * @var \SuperV\Platform\Domains\Routing\RouteRegistrar
     */
    protected $loader;

    public function __construct(RouteRegistrar $loader)
    {
        $this->loader = $loader;
    }

    public function loadFromPath($path) {
        if (!starts_with($path, '/')) {
            $path = base_path($path);
        }
        if ($folders = glob("{$path}/*", GLOB_ONLYDIR)) {
            foreach ($folders as $folder) {
                try {
                    $this->loader->setPort($port = pathinfo($folder, PATHINFO_BASENAME));
                } catch (\Exception $e) {
                    // a port with the folder name could not be found
                    continue;
                }

                $files = glob("{$folder}/*.php");
                foreach($files as $file) {
                    $routes = (array)require $file;
                        $this->loader->register($routes);
                }
            }
        } elseif($files = glob(base_path("{$path}/*.php"))) {
        }

    }

    public function loadFromFile($file)
    {
        $routes = require base_path($file);
        $this->loader->register($routes);
    }
}