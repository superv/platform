<?php

namespace SuperV\Platform\Exceptions;

use RuntimeException;
use Throwable;

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

    public static function throw(Throwable $e)
    {
        throw new static($e->getMessage(), $e->getCode(), $e);
    }

    public static function runtime(?string $msg)
    {
        throw new RuntimeException($msg);
    }
}
