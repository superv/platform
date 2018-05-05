<?php

namespace SuperV\Platform\Domains\Feature;

use SuperV\Platform\Support\Collection;

abstract class AbstractFeature implements Feature
{
    /** @var \SuperV\Platform\Domains\Feature\Request */
    protected $request;

    /** @var \SuperV\Platform\Support\Collection */
    protected $params;

    public function init()
    {
        $this->params = new Collection;

        return $this;
    }

    public function getParam($key, $default = null)
    {
        return $this->params->get($key, $default);
    }

    public function setParam($key, $value)
    {
        return $this->params->put($key, $value);
    }

    public function __call($name, $arguments)
    {
        if (starts_with($name, 'get')) {
            $key = snake_case(str_replace('get', '', $name));
            if ($this->params->has($key)) {
                return $this->params->get($key);
            }
        }

        throw new \InvalidArgumentException('Unknown method '.$name);
    }


    public function getResponseData()
    {
        return [];
    }

    /**
     * @param \SuperV\Platform\Domains\Feature\Request $request
     * @return AbstractFeature
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}