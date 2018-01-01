<?php

namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Http\Request;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\Port\Port;
use SuperV\Platform\Domains\Droplet\Port\PortCollection;
use SuperV\Platform\Traits\RegistersRoutes;

class DetectActivePort
{
    use RegistersRoutes;

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

        app()->instance(Port::class, $port);

        if ($themeSlug = $port->getTheme()) {
            if ($theme = app(DropletFactory::class)->fromSlug($themeSlug)) {
                $viewsPath = [base_path($theme->getPath('resources/views'))];
            }
        }

        $view->addNamespace('port', $viewsPath ?? [base_path($port->getPath('resources/views'))]);
    }
}
