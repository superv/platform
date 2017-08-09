<?php namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class DetectCurrentPortJob
{
    /**
     * @var Droplets
     */
    private $droplets;

    public function __construct(Droplets $droplets)
    {
        $this->droplets = $droplets;
    }

    public function handle(RouteMatched $event)
    {
        /** @var Route $route */
        if (!$route = $event->route) {
            return;
        }

        \Log::info('current route', $event->route->getAction());

//        dd($route->getAction());

        if (!$slug = array_get($route->getAction(), 'superv::port')) {
            return;
        }

        /** @var DropletModel $port */
        $port = $this->droplets->withSlug($slug);

        superv('view')->addNamespace(
            'port',
            [base_path($port->getPath('resources/views'))]
        );
    }
}