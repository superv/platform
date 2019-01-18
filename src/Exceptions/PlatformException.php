<?php

namespace SuperV\Platform\Exceptions;

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

    public static function runtime(?string $msg)
    {
        throw new RuntimeException($msg);
    }
}