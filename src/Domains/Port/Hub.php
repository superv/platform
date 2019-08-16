<?php

namespace SuperV\Platform\Domains\Port;

use Illuminate\Support\Collection;

class Hub
{
    /**
     * Registered ports
     *
     * @var \Illuminate\Support\Collection
     */
    protected $ports;

    /**
     * @var \SuperV\Platform\Domains\Port\Port
     */
    protected $defaultPort;

    public function __construct(Collection $ports)
    {
        $this->ports = $ports;
    }

    /**
     * Register the port, resolve if class name is provided
     * and return the resolved instance.
     *
     * @param \SuperV\Platform\Domains\Port\Port $port
     * @return mixed
     */
    public function register($port)
    {
        if (is_string($port)) {
            $port = resolve($port);
        }

        $this->ports->push($port);

        return $port;
    }

    public function registerDefaultPort(): void
    {
        if ($port = sv_config('ports.default')) {
            $this->defaultPort = resolve($port);
            $this->register($this->defaultPort);
        }
    }

    public function getDefaultPort(): ?Port
    {
        return $this->defaultPort;
    }

    /**
     * Return currently registered ports
     *
     * @return \Illuminate\Support\Collection
     */
    public function ports()
    {
        return $this->ports;
    }

    /**
     * Get port by slug
     *
     * @param $slug
     * @return \SuperV\Platform\Domains\Port\Port
     */
    public function get($slug)
    {
        return $this->ports->first(function (Port $port) use ($slug) {
            return $port->slug() === $slug;
        });
    }
}