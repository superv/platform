<?php

namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
use SuperV\Platform\Domains\Droplet\Port\ActivePort;
use SuperV\Platform\Domains\Droplet\Port\PortCollection;

class DetectActivePort
{
    /**
     * @var Droplets
     */
    private $droplets;

    /**
     * @var \SuperV\Platform\Domains\Droplet\Port\PortCollection
     */
    private $ports;

    public function __construct(Droplets $droplets, PortCollection $ports)
    {
        $this->droplets = $droplets;
        $this->ports = $ports;
    }

    public function handle(RouteMatched $event)
    {
        /** @var Route $route */
        if (! $route = $event->route) {
            return;
        }

        if (! $port = $this->ports->byHostname($event->request->getHttpHost())) {
            throw new \LogicException('This should not happen!: '.$event->request->getHttpHost());
        }

        app()->bindIf(ActivePort::class, function() use($port) { return $port; }, true);

        app('view')->addNamespace('port', [base_path($port->getPath('resources/views'))]);
    }
}
