<?php

namespace SuperV\Platform\Domains\Feature;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Exceptions\ValidationException;
use SuperV\Platform\Support\Collection;

abstract class AbstractFeatureRequest implements Request
{
    /** @var \SuperV\Platform\Contracts\Validator */
    protected $validator;

    /** @var \SuperV\Platform\Support\Collection */
    protected $params;

    /**
     * @var \SuperV\Platform\Domains\Feature\Feature
     */
    protected $feature;

    public function __construct(Validator $validator, Feature $feature)
    {
        $this->validator = $validator;

        $this->feature = $feature;
    }

    public function init($params)
    {
        $this->params = new Collection($params);

        return $this;
    }

    protected function throwValidationError($message, $key = 0)
    {
        throw (new ValidationException())->setErrors([$key => $message]);
    }

    public function getParam($key, $default = null)
    {
        return $this->params->get($key, $default);
    }

    protected function setParam($key, $value)
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
}