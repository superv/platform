<?php

namespace SuperV\Platform\Exceptions;

class ValidationException extends \Exception
{
    protected $errors;

    protected $data;

    protected $rules;

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public static function error($key, $message) {
        return (new  self())->setErrors([$key => $message]);
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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }
}
