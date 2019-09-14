<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

class Hook
{
    protected static $map = [];

    protected static $locks = [];

    public static function register($handle, $base)
    {
        static::$map[$handle] = $base;
    }

    public static function base($handle)
    {
        return static::$map[$handle] ?? null;
    }

    public static function unregister($handle)
    {
        unset(static::$map[$handle]);
    }

    public static function saving(EntryContract $entry, Resource $resource)
    {
        if (! $baseNamespace = static::base($resource->getIdentifier())) {
            return;
        }

        if ($resourceKey = $resource->config()->getResourceKey()) {
            $plural = str_plural($resourceKey);
        } else {
            $plural = $resource->getName();
        }

        $listener = $baseNamespace."\\".studly_case($plural.'_saving');
        if (class_exists($listener)) {
            $listener = app()->make($listener);
            if (method_exists($listener, 'before')) {
                call_user_func_array([$listener, 'before'], [$entry, $resource]);
            }
        }
    }

    public static function saved(EntryContract $entry, Resource $resource)
    {
        if (! $baseNamespace = static::base($resource->getIdentifier())) {
            return;
        }

        if ($resourceKey = $resource->config()->getResourceKey()) {
            $plural = str_plural($resourceKey);
        } else {
            $plural = $resource->getIdentifier();
        }

        $listener = $baseNamespace."\\".studly_case($plural.'_saving');
        // check lock
        //
        $lock = md5($listener);
        if (isset(static::$locks[$lock])) {
            return;
        }
        if (class_exists($listener)) {
            $listener = app()->make($listener);

            if (method_exists($listener, 'after')) {
                static::$locks[$lock] = true;
                call_user_func_array([$listener, 'after'], [$entry, $resource]);
                unset(static::$locks[$lock]);
            }
        }
    }

    public static function attributes($handle, array $attributes)
    {
        if (! $baseNamespace = static::$map[$handle] ?? null) {
            return $attributes;
        }

        if ($resourceKey = $attributes['config']['resource_key']) {
            $plural = str_plural($resourceKey);
        } else {
            $plural = explode('::', $handle)[1];
        }

        $configClass = $baseNamespace."\\".studly_case($plural.'_config');

        if (class_exists($configClass)) {
            $attributes['config'] = $configClass::make($attributes['config']);
        }

        return $attributes;
    }

    public static function resource(Resource $resource)
    {
        if (! $base = static::$map[$resource->getIdentifier()] ?? null) {
            return;
        }

        $base = new $base($resource);

        if (method_exists($base, 'handle')) {
            $base->handle($resource);
        }
    }
}
