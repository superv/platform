<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Droplet\Port\PortCollection;

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

    public function handle(Request $request, PortCollection $ports)
    {

        $currentHostname = trim(str_replace(['http://', 'https://'], '', $request->root()), '/');
        if ($port = $ports->byHostname($currentHostname)) {
            $portName = $port->getName();

            $this->mergeRouteFile(base_path($this->provider->getPath("routes/{$portName}.php")));

            foreach ($this->routes as $route => &$data) {
                if (! is_array($data)) {
                    $data = ['uses' => $data];
                }

                array_set($data, 'superv::port', $port->getSlug());
            }
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
