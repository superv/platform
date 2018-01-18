<?php

namespace SuperV\Platform;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Platform\Domains\Droplet\Feature\IntegrateDroplet;
use SuperV\Platform\Domains\Droplet\Jobs\GetPortRoutes;
use SuperV\Platform\Domains\Droplet\Module\Jobs\ActivatePort;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActivePort;
use SuperV\Platform\Events\DropletsBooted;
use SuperV\Platform\Events\PlatformReady;

class Platform
{
    use DispatchesJobs;

    public function boot()
    {
        $dropletManager = app(DropletManager::class);

        /**
         * Refactor idea: instead of registering routes views etc
         * by looping all droplets, first collect the droplets
         * then perform registeration depending on port, cli
         */
        $dropletManager->load();

        if ($port = $this->dispatch(new DetectActivePort())) {
            $this->dispatch(new ActivatePort($port));
            $this->dispatch(new IntegrateDroplet($port));

            $routes = superv('routes')->byPort($port->getSlug());
            $port->registerRoutes($routes);
            $platformRoutes = $this->dispatch(new GetPortRoutes(platform_path()));
            $port->registerRoutes($platformRoutes);

            config()->set('auth.defaults.guard', strtolower($port->getName()));
        }

        $dropletManager->boot();
        DropletsBooted::dispatch();
        PlatformReady::dispatch();
    }
}
