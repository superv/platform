<?php

namespace SuperV\Platform\Domains\Feature;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Exceptions\ValidationException;

abstract class AbstractFeatureRequest implements Request
{
    /** @var \SuperV\Platform\Contracts\Validator */
    protected $validator;

    /** @var array */
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
        $this->params = $params;

        return $this;
    }

    public function validate($input, $rules)
    {
        if (method_exists($input, 'toArray')) {
            $input = $input->toArray();
        }
        if (! is_array($input)) {
            $this->throwValidationError('Invalid input data');
        }
        $this->validator->make($input, $rules);

        // return only validated input
        $validated = collect($input)
            ->only(
                collect($rules)->keys()
                               ->map(function ($rule) {
                                   return explode('.', $rule)[0];
                               })
                               ->unique()
                               ->toArray()
            )->toArray();

        $this->feature->setParam('validated', $validated);

        return $validated;
    }

    public function getParam($key, $default = null)
    {
        return array_get($this->params, $key, $default);
    }

    public function throwValidationError($message, $key = 0)
    {
        throw (new ValidationException())->setErrors([$key => $message]);
    }

    protected function setParam($key, $value)
    {
        return array_set($this->params, $key, $value);
    }

    protected function transfer($key, $as = null)
    {
        $this->feature->setParam($as ?? $key, $this->getParam($key));
    }

    public function __call($name, $arguments)
    {
        if (starts_with($name, 'get')) {
            $key = snake_case(str_replace('get', '', $name));
            if (array_has($this->params, $key)) {
                return array_get($this->params, $key);
            }
        }

        throw new \InvalidArgumentException('Unknown method '.$name);
    }
}