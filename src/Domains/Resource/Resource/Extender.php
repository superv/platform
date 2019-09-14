<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;

/**
 * Class Extender
 * Extends resource without the need of an extension file
 *
 * @package SuperV\Platform\Domains\Resource\Resource
 */
class Extender implements ExtendsResource
{
    protected $identifier;

    protected $callback;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    public function extend(Resource $resource)
    {
         ($this->callback)($resource);
    }

    public function extends(): string
    {
        return $this->identifier;
    }

    public function with(Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }
}
