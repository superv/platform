<?php

namespace SuperV\Platform\Exceptions;

class ValidationException extends \Exception
{
    protected $errors;

    protected $data;

    protected $rules;

    public function all()
    {
        return [
            'data'   => $this->getData(),
            'rules'  => $this->getRules(),
            'errors' => $this->getErrors(),
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return ValidationException
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param mixed $rules
     * @return ValidationException
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    public function getErrorsAsString()
    {
        return json_encode($this->all());
    }

    public function toResponse()
    {
        return response()->json([
            'errors' => $this->errors,
        ], 422);
    }

    public static function error($key, $message)
    {
        return (new  self())->setErrors([$key => $message]);
    }

    public static function throw($key, $message)
    {
        throw static::error($key, $message);
    }
}
