<?php

namespace SuperV\Platform\Exceptions;

use Exception;
use Illuminate\Database\QueryException;
use RuntimeException;

class PlatformException extends \Exception
{
    public function toResponse()
    {
        return response()->json([
            'error' => [
                'description' => $this->getMessage(),
            ],
        ], 400);
    }

    public static function fail(?string $msg)
    {
        throw new static($msg);
    }

    public static function throw(Exception $e)
    {
        $code = ($e instanceof QueryException) ? '0' : $e->getCode();
        throw new static($e->getMessage(), $code, $e);
    }

    public static function runtime(?string $msg)
    {
        throw new RuntimeException($msg);
    }
}
