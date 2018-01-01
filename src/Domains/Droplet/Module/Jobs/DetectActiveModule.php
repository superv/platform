<?php

namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Domains\Droplet\Droplets;
use SuperV\Platform\Domains\Droplet\Module\Module;

class DetectActiveModule
{
    /**
     * @var Droplets
     */
    private $droplets;

    /**
     * @var Factory
     */
    private $view;

    public function __construct(DropletCollection $droplets, Factory $view)
    {
        $this->droplets = $droplets;
        $this->view = $view;
    }

    public function handle(RouteMatched $event)
    {
        /** @var Route $route */
        if (! $route = $event->route) {
            return;
        }

        if (! $slug = array_get($route->getAction(), 'superv::droplet')) {
            return;
        }

        /** @var Module $module */
        $module = $this->droplets->get($slug);

        $this->view->addNamespace('module', [base_path($module->getPath('resources/views'))]);
    }
}
