<?php

namespace SuperV\Platform\Domains\Feature;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;

abstract class AbstractFeature implements Feature
{
    use DispatchesJobs;

    /** @var \SuperV\Platform\Domains\Feature\Request */
    protected $request;

    /** @var \Illuminate\Support\Collection */
    protected $params;

    /**
     * @var \SuperV\Platform\Domains\Feature\Response
     */
    protected $response;

    /**
     * Validated feature request
     * @var array
     */
    protected $validated;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

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
        if (method_exists($this, 'set'.studly_case($key))) {
            call_user_func_array([$this, 'set'.studly_case($key)], [$value]);
        } elseif (property_exists(get_class($this), camel_case($key))) {
            $this->{camel_case($key)} = $value;
        }

        $this->params->put($key, $value);
    }

    public function __call($name, $arguments)
    {
        if (starts_with($name, 'get')) {
            $key = snake_case(str_replace('get', '', $name));
            if ($this->params->has($key)) {
                return $this->params->get($key);
            }
        } elseif (starts_with($name, 'set')) {
            $key = snake_case(str_replace('set', '', $name));

            return $this->params->put($key, $arguments[0]);
        }

        throw new \InvalidArgumentException('Unknown method '.$name);
    }

    public function getResponseData()
    {
        return [];
    }

//    /**
//     * @param \SuperV\Platform\Domains\Feature\Request $request
//     * @return AbstractFeature
//     */
//    public function setRequest(Request $request)
//    {
//        $this->request = $request;
//
//        return $this;
//    }
}