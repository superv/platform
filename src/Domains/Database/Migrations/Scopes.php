<?php

namespace SuperV\Platform\Domains\Database\Migrations;

class Scopes
{
    protected static $scopes = [];

    public static function register($key, $path)
    {
        if (! $_path = realpath($path)) {
            return;
        }
        static::$scopes[$key] = realpath($_path);
    }

    public static function scopes()
    {
        return static::$scopes;
    }

    public static function key($path)
    {
        return array_get(array_flip(static::$scopes), $path);
    }

    public static function path($key)
    {
        return array_get(static::$scopes, $key);
    }
}