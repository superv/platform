<?php

namespace SuperV\Platform\Domains\Port;

use SuperV\Platform\Support\Collection;

class Hub
{
    /**
     * @var \SuperV\Platform\Support\Collection
     */
    protected $ports;

    public function __construct(Collection $ports)
    {
        $this->ports = $ports;
    }

    /**
     * Register the port, resolve if class name is provided
     * and return the resolved instance.
     *
     * @param $port
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

    /** @return \SuperV\Platform\Support\Collection */
    public function ports()
    {
        return $this->ports;
    }

    /**
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