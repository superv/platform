<?php

namespace SuperV\Platform\Domains\Nucleo;

class Nucleo
{
    protected static $modelMap = [];

    public static function modelMap($modelMap)
    {
        static::$modelMap = $modelMap;
    }

    public static function modelOfTable($table)
    {
        return static::$modelMap[$table];
    }
}