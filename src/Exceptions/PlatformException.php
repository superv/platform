<?php

namespace SuperV\Platform\Exceptions;

use Exception;
use Illuminate\Database\QueryException;
use RuntimeException;

class PlatformException extends \Exception
{
    protected $payload;

    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    public function toResponse()
    {
        return response()->json([
            'error' => array_filter([
                'description' => $this->getMessage(),
                'payload'     => $this->payload,
                'trace'       => $this->getTrace(),
            ]),
        ], 500);
    }

    public static function fail(?string $msg)
    {
        throw new static($msg);
    }

    public static function debug($payload)
    {
        throw (new static)->setPayload($payload);
    }

    public static function throw(Exception $e)
    {
        $code = ($e instanceof QueryException) ? '0' : $e->getCode();
        $message = ($e instanceof ValidationException) ? $e->getErrorsAsString() : $e->getMessage();
        throw new static($message, $code, $e);
    }

    public static function runtime(?string $msg)
    {
        throw new RuntimeException($msg);
    }
}
