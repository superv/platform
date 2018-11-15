<?php

namespace SuperV\Platform\Support;

class Path
{
    /**
     * Parse  class from file path
     *
     * @param $namespace
     * @param $namespaceBasePath
     * @param $checkPath
     */
    public static function parseClass($namespace, $namespaceBasePath, $checkPath): string
    {
        return $namespace.'\\'.str_replace([$namespaceBasePath.'/', '.php', '/'], ['', '', '\\'], $checkPath);
    }
}