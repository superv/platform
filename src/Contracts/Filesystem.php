<?php

namespace SuperV\Platform\Contracts;

interface Filesystem
{
    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false);

    public function get($path, $lock = false);

    public function sharedGet($path);

    public function exists($path);

    public function put($path, $contents, $lock = false);
}
