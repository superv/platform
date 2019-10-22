<?php

namespace SuperV\Platform\Domains\Auth\Access;

use Exception;

class AuthorizationFailedException extends Exception
{
    public static function action($action)
    {
        throw new static("Authorization check failed on action [{$action}]", 403);
    }
}