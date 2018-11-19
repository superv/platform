<?php

namespace SuperV\Platform\Domains\Context;

class Context
{
    protected $items = [];

    public function __construct($items = [])
    {
        $this->items = is_array($items) ? $items : func_get_args();
    }

    public function add($item)
    {
        $this->items[] = $item;

        return $this;
    }

    public function apply()
    {
        Negotiator::deal($this->items);
    }
}