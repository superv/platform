<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Droplet\DropletServiceProvider;
use SuperV\Platform\Domains\Droplet\Types\PortCollection;

class PackDropletRoutesJob
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

        // $port = $this->run(new GetPortFromRequest()

        $hostname = trim(str_replace(['http://', 'https://'], '', $request->root()), '/');
        if ($port = $ports->byHostname($hostname)) {
            $portName = $port->getName();
            $routesFile = base_path($this->provider->getPath("routes/{$portName}.php"));
            if (file_exists($routesFile)) {
                $include = require $routesFile;
                if (is_array($include)) {
                    $routes = array_merge($include, $routes);
                }
            }
        }

        return $routes;
    }
}