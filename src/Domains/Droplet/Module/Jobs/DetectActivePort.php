<?php

namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Droplet\Port\Ports;

class DetectActivePort
{
    use DispatchesJobs;

    public function handle(Request $request, Ports $ports)
    {
        if (app()->runningInConsole()) {
            return null;
        }
        $httpHost = $request->getHttpHost();
        $requestUri = $request->getRequestUri();
        if (! $port = $ports->byRequest($httpHost, $requestUri)) {
            return null;
        }

        return $port;
    }
}
