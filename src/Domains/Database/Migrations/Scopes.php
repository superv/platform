<?php

namespace SuperV\Platform\Domains\Database\Migrations;

class Scopes
{
    protected static $scopes = [];

    public static function register($key, $path)
    {
        array_set(static::$scopes, $key, $path);
    }

    public static function scopes()
    {
        return static::$scopes;
    }

    public static function path($key)
    {
        return array_get(static::$scopes, $key);
    }

}