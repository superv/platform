<?php

namespace SuperV\Platform\Contracts;

interface Collection
{
    public function get($key, $default = null);

    public function put($key, $value);

    public function reduce(callable $callback, $initial = null);
}
