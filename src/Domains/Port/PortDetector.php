<?php

namespace SuperV\Platform\Domains\Port;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Platform;

class PortDetector
{
    use DispatchesJobs;

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function detect($request)
    {
        if ($portSlug = $this->detectFor(
            $request->getHttpHost(),
            $request->getRequestUri()
        )) {
            PortDetectedEvent::dispatch(Port::fromSlug($portSlug));
        }
    }

    public function detectFor($httpHost, $requestUri)
    {
        $ports = Platform::config('ports');
        $requestUri = ltrim($requestUri, '/');

        foreach ($ports as $slug => $port) {
            if ($port['hostname'] !== $httpHost) {
                continue;
            }

            if ($prefix = array_get($port, 'prefix')) {
                if ($requestUri && starts_with($requestUri, $prefix)) {
                    return $slug;
                }
            }
        }

        foreach ($ports as $slug => $port) {
            if ($port['hostname'] === $httpHost) {
                return $slug;
            }
        }

        return null;
    }
}