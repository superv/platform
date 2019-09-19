<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Support\Concerns\Hydratable;

class ResourceDriver implements Arrayable
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

    public function setParam($key, $value): ResourceDriver
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'type'   => $this->type,
            'params' => $this->params,
        ];
    }

    public function toDsn()
    {
        return sprintf("%s@%s://%s", $this->getType(), $this->getParam('connection'), $this->getParam('table'));
    }
}
