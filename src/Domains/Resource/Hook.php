<?php

namespace SuperV\Platform\Domains\Resource;

class Hook
{
    protected static $map = [];

    public static function register($handle, $base)
    {
        static::$map[$handle] = $base;
    }

    public static function base($handle)
    {
       return   static::$map[$handle] ??  null;
    }

    public static function unregister($handle)
    {
        unset(static::$map[$handle]);
    }

    public static function attributes($handle, array $attributes)
    {
        if (! $baseNamespace = static::$map[$handle] ?? null) {
            return $attributes;
        }


        if ($resourceKey = $attributes['config']['resource_key']) {
            $plural = str_plural($resourceKey);
        } else {
            $plural = $handle;
        }

        $configClass = $baseNamespace . "\\". studly_case($plural.'_config');
        if (class_exists($configClass)) {
            $attributes['config'] = $configClass::make($attributes['config']);
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