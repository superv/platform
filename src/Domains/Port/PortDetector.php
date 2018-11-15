<?php

namespace SuperV\Platform\Domains\Port;

use Hub;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PortDetector
{
    use DispatchesJobs;

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function detect($request)
    {
        if ($portSlug = $this->detectFor($request->getHttpHost(), $request->getRequestUri())) {
            PortDetectedEvent::dispatch(Hub::get($portSlug));
        }
    }

    public function detectFor($httpHost, $requestUri)
    {
        $ports = Hub::ports();
        $requestUri = ltrim($requestUri, '/');

        /** @var \SuperV\Platform\Domains\Port\Port $port */
        foreach ($ports as $port) {
            if ($port->hostname() !== $httpHost) {
                continue;
            }

            if ($prefix = $port->prefix()) {
                if ($requestUri && starts_with($requestUri, $prefix)) {
                    return $port->slug();
                }
            }
        }

        foreach ($ports as $port) {
            if ($port->hostname() === $httpHost) {
                return $port->slug();
            }
        }

        return null;
    }
}