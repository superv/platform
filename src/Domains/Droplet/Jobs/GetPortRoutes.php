<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Droplet\Types\PortCollection;
use SuperV\Platform\Domains\Droplet\DropletServiceProvider;

/**
 * Class GetPortRoutes.
 *
 * Determines the current Port from hostname, and returns
 * relevant routes for that Port
 */
class GetPortRoutes
{
    /**
     * @var \SuperV\Platform\Domains\Droplet\DropletServiceProvider
     */
    private $provider;

    public function __construct(DropletServiceProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(Request $request, PortCollection $ports)
    {
        $routes = [];

        $currentHostname = trim(str_replace(['http://', 'https://'], '', $request->root()), '/');
        if ($port = $ports->byHostname($currentHostname)) {
            $portName = $port->getName();
            $routesFile = base_path($this->provider->getResourcePath("routes/{$portName}.php"));
            if (file_exists($routesFile)) {
                $include = require $routesFile;
                if (is_array($include)) {
                    $routes = array_merge($include, $routes);

                    if (! empty($routes)) {
                        foreach ($routes as $route => &$data) {
                            if (! is_array($data)) {
                                $data = ['uses' => $data];
                            }

                            array_set($data, 'superv::port', $port->getSlug());
                        }
                    }
                }
            }
        }

        return $routes;
    }
}
