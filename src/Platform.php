<?php

namespace SuperV\Platform;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Platform\Domains\Droplet\Feature\IntegrateDroplet;
use SuperV\Platform\Domains\Droplet\Jobs\LoadRouteFiles;
use SuperV\Platform\Domains\Droplet\Module\Jobs\ActivatePort;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActivePort;
use SuperV\Platform\Domains\Droplet\Port\Routes;
use SuperV\Platform\Events\PlatformReady;

class Platform
{
    use DispatchesJobs;

    /**
     * @var Routes
     */
    protected $routes;

    /**
     * @var DropletManager
     */
    protected $manager;

    public function __construct(Routes $routes, DropletManager $manager)
    {
        $this->routes = $routes;
        $this->manager = $manager;
    }

    public function boot()
    {
        $this->manager->load();

        $this->handleActivePort();

        $this->manager->boot();

        PlatformReady::dispatch();
    }

    protected function handleActivePort()
    {
        if ($port = $this->dispatch(new DetectActivePort())) {
            $this->dispatch(new ActivatePort($port));
            $this->dispatch(new IntegrateDroplet($port));

            $routes = $this->routes->byPort($port->getSlug());
            $port->registerRoutes($routes);
            $platformRoutes = $this->dispatch(new LoadRouteFiles(platform_path()));
            $port->registerRoutes($platformRoutes);

            config()->set('auth.defaults.guard', strtolower($port->getName()));
        }
    }
}
