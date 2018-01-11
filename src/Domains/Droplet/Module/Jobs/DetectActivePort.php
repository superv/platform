<?php

namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\Port\Port;
use SuperV\Platform\Domains\Droplet\Port\PortCollection;
use SuperV\Platform\Traits\RegistersRoutes;

class DetectActivePort
{
    use RegistersRoutes;
    use DispatchesJobs;

    public function handle(Request $request, PortCollection $ports, Factory $view)
    {
        if (app()->runningInConsole()) {
            return;
        }
        $httpHost = $request->getHttpHost();
        $requestUri = $request->getRequestUri();
        if (! $port = $ports->byRequest($httpHost, $requestUri)) {
            //\Log::warning("Unknown hostname {$httpHost}, can not detect active port");
            return;
        }

        $this->dispatch(new ActivatePort($port));
    }
}
