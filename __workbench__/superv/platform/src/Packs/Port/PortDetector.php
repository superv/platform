<?php

namespace SuperV\Platform\Packs\Port;

use Platform;

class PortDetector
{
    /**
     * @param \Illuminate\Http\Request $request
     */
    public function detect($request)
    {
        if ($port = $this->detectFor(
            $request->getHttpHost(),
            $request->getRequestUri()
        )) {
//            Platform::setActivePort($port);

            PortDetectedEvent::dispatch($port);
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