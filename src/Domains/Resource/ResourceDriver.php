<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Support\Concerns\Hydratable;

class ResourceDriver
{
    use Hydratable;

    protected $type;

    protected $params;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

    public function getParam($key)
    {
        return array_get($this->params, $key);
    }

    public function getType()
    {
        return $this->type;
    }
}
