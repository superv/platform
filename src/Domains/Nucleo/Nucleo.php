<?php

namespace SuperV\Platform\Domains\Nucleo;

class Nucleo
{
    protected static $modelMap = [];

    protected static $resourceMap = [];

    public static function modelMap($modelMap)
    {
        static::$modelMap = $modelMap;
    }

    public static function modelOfTable($table)
    {
        return static::$modelMap[$table];
    }

    public static function resourceMap($resourceMap)
    {
        static::$resourceMap = $resourceMap;
    }

    public static function resourceBySlug($slug)
    {
        return static::$resourceMap[str_replace('-', '_', $slug)];
    }
}