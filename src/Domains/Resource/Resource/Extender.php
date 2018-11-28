<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;

class Extender implements ExtendsResource
{
    protected $handle;

    protected $callback;

    public function __construct($handle)
    {
        $this->handle = $handle;
    }

    public function extend(Resource $resource)
    {
         ($this->callback)($resource);
    }

    public function extends(): string
    {
        return $this->handle;
    }

    public function with(Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }
}