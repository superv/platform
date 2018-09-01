<?php

namespace SuperV\Platform\Support;

class Path
{
    public static function parseClass($namespace, $namespaceBasePath, $checkPath)
    {
//        $relativePath = ltrim(str_replace(base_path(), '', $checkPath), '/');
        return $namespace.'\\'.str_replace([$namespaceBasePath.'/', '.php', '/'], ['', '', '\\'], $checkPath);
    }
}