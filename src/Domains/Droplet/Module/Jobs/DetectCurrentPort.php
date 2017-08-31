<?php

namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
use SuperV\Platform\Domains\Droplet\Types\PortCollection;
use SuperV\Platform\Http\Middleware\MiddlewareCollection;

class DetectCurrentPort
{
    /**
     * @var Droplets
     */
    private $droplets;

    /**
     * @var PortCollection
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

        //if (! $slug = array_get($route->getAction(), 'superv::port')) {
        //    if (! $port = $this->ports->byHostname($event->request->getHttpHost())) {
        //        return;
        //    } else {
        //        $collection = app(MiddlewareCollection::class);
        //        if ($middlewares = $collection->get($port->getSlug())) {
        //            $route->middleware($middlewares);
        //        }
        //    }
        //} else {
        //    /** @var DropletModel $port */
        //    $port = $this->droplets->withSlug($slug);
        //}

        if (! $port = $this->ports->byHostname($event->request->getHttpHost())) {
            throw new \LogicException('This should not happen!: '.$event->request->getHttpHost());
        }

        // Add current ports default middlewares to route
        $collection = app(MiddlewareCollection::class);
        if ($middlewares = $collection->get($port->getSlug())) {
            $route->middleware($middlewares);
        }

        app('view')->addNamespace('port', [base_path($port->getPath('resources/views'))]);
    }
}
