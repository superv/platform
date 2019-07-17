<?php

namespace SuperV\Platform\Domains\Resource;

class Hook
{
    protected static $map = [];

    public static function register($handle, $base)
    {
        static::$map[$handle] = $base;
    }

    public static function unregister($handle)
    {
        unset(static::$map[$handle]);
    }

    public static function attributes($handle, array $attributes)
    {
        if (! $base = static::$map[$handle] ?? null) {
            return $attributes;
        }

        $base = new $base();

        if (method_exists($base, 'config')) {
            $base->config($attributes['config']);
        }

        return $attributes;
    }

    public static function resource(Resource $resource)
    {
        if (! $base = static::$map[$resource->getHandle()] ?? null) {
            return;
        }

        $base = new $base($resource);

        if (method_exists($base, 'handle')) {
            $base->handle($resource);
        }
    }
}