<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\Port\Port;

/**
 * Class GetPortRoutes.
 *
 * Determines the current Port from hostname,
 * and returns relevant routes
 */
class GetPortRoutes
{
    /**
     * @var \SuperV\Platform\Domains\Droplet\DropletServiceProvider
     */
    private $provider;

    private $routes = [];

    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    public function handle(Port $activePort)
    {
        $portName = $activePort->getName();

        $this->mergeRouteFile(base_path($this->provider->getPath("routes/{$portName}.php")));

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
