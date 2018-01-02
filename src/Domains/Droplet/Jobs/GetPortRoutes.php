<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\Port\Port;

class GetPortRoutes
{
    private $path;

    private $routes = [];

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function handle(Port $activePort)
    {
        $portName = $activePort->getName();

        $this->mergeRouteFile(base_path($this->path . "/routes/{$portName}.php"));

        if ($routeFiles = glob(base_path($this->path . "/routes/{$portName}/*.php"))) {
            foreach($routeFiles as $file) {
                $this->mergeRouteFile($file);
            }
        }

        foreach ($this->routes as &$data) {
            if (! is_array($data)) {
                $data = ['uses' => $data];
            }

            array_set($data, 'superv::port', $activePort->getSlug());
        }

        return $this->routes;
    }

    protected function mergeRouteFile($routesFile)
    {
        if (file_exists($routesFile)) {
            $include = require $routesFile;
            if (is_array($include)) {
                $this->routes = array_merge($include, $this->routes);
            }
        }
    }
}
