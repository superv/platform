<?php

namespace SuperV\Platform\Domains\Auth\Access;

use Exception;

class AuthorizationFailedException extends Exception
{
    public static function actionFailed($action)
    {
        throw new self("Authorization check failed on action [{$action}]");
    }
}