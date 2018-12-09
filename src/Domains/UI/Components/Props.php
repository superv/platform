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

    public function get($key)
    {
        return array_get($this->props, $key);
    }

    public function set($key, $value): Props
    {
        array_set($this->props, $key, $value);

        return $this;
    }

    public function push($value, $to)
    {
        if (! $target = $this->get($to)) {
            $target = [];
        }

        $target[] = $value;

        $this->set($to, $target);
    }

    public function merge(array $props)
    {
        $this->props = array_merge($this->props, $props);

        return $this;
    }

    public function transform(Closure $callback)
    {
        foreach ($this->props as $key => $value) {
            $this->props[$key] = $callback($value);
        }

        return $this;
    }

    public function compose(Tokens $tokens = null)
    {
        $props = [];
        // prepare keys for vue props
        //
        foreach ($this->props as $key => $value) {
            $props[str_replace('_', '-', $key)] = $value;
        }

        return $props;
    }
}