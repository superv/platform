<?php

namespace SuperV\Platform\Adapters;

use Illuminate\Validation\Factory;
use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Exceptions\ValidationException;

class LaravelValidator implements Validator
{
    /**
     * @var Factory
     */
    protected $factory;

    /** @var \Illuminate\Validation\Validator */
    protected $baseValidator;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function make(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        $this->baseValidator = $this->factory->make($data, $rules, $messages, $customAttributes);

        if ($this->baseValidator->fails()) {
            $exception = new ValidationException();
            $exception->setErrors($this->errors());
            $exception->setData($data);
            $exception->setRules($rules);
            throw $exception;
        }

        // return only validated input
        return collect($data)
            ->only(
                collect($rules)->keys()
                               ->map(function ($rule) {
                                   return explode('.', $rule)[0];
                               })
                               ->unique()
                               ->toArray()
            )->toArray();
    }

    public function errors()
    {
        $errors = $this->baseValidator->errors();

        $messages = [];
        foreach ($errors->messages() as $key => $message) {
            $messages[$key] = $message;
        }

        return $messages;
    }

    public function fails()
    {
        return $this->baseValidator->fails();
    }
}
