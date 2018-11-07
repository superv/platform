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

    public function toResponse()
    {
        return response()->json([
            'errors' => $this->errors,
        ], 400);
    }

    public static function error($key, $message)
    {
        return (new  self())->setErrors([$key => $message]);
    }
}
