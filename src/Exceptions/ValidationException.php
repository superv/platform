<?php

namespace SuperV\Platform\Exceptions;

class ValidationException extends \Exception
{
    protected $errors;

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }
}
