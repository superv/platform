<?php namespace SuperV\Platform\Contracts;

interface Filesystem
{
    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false);
}