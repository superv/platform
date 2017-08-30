<?php

namespace SuperV\Platform\Domains\Droplet\Port;

use SuperV\Platform\Domains\Droplet\DropletServiceProvider;
use SuperV\Platform\Http\Middleware\MiddlewareCollection;

class PortServiceProvider extends DropletServiceProvider
{
    protected $middlewares = [];

    public function register(MiddlewareCollection $middlewares)
    {
        $port = $this->getDroplet()->getSlug();

        $middlewares->put($port, $this->middlewares);
    }
}
