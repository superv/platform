<?php

namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Http\Request;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Droplet\Port\ActivePort;
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
        if (! $port = $ports->byHostname($request->getHttpHost())) {
            throw new \LogicException('This should not happen!: '.$request->getHttpHost());
        }

        app()->bindIf(ActivePort::class, function () use ($port) { return $port; }, true);

        $view->addNamespace('port', [base_path($port->getPath('resources/views'))]);

        $this->registerRoutes($port);
    }
}
