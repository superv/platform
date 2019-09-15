<?php

namespace SuperV\Platform\Domains\Resource\Events;

use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Support\Fireable;

class ResourceConfigResolvedEvent
{
    use Fireable;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceConfig
     */
    protected $config;

    public function __construct(ResourceConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\ResourceConfig
     */
    public function config(): \SuperV\Platform\Domains\Resource\ResourceConfig
    {
        return $this->config;
    }
}
