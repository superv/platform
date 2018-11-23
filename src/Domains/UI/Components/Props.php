<?php

namespace SuperV\Platform\Domains\UI\Components;

use Closure;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Tokens;

class Props implements Composable
{
    protected $props = [];

    public function __construct(array $props = [])
    {
        $this->props = $props;
    }

    public function merge(array $props)
    {
        $this->props = array_merge($this->props, $props);

        return $this;
    }

    public function transform(Closure $callback)
    {
        foreach($this->props as $key => $value) {
            $this->props[$key] = $callback($value);

        }

        return $this;
    }

    public function compose(Tokens $tokens = null)
    {
        return $this->props;
    }
}