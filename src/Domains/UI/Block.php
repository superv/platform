<?php

namespace SuperV\Platform\Domains\UI;


use Illuminate\Contracts\Support\Arrayable;

class Block implements Arrayable
{
    protected $component;

    /** @var array */
    protected $props;

    public static function make(string $component)
    {
        $block = new static;
        $block->component = $component;

        return $block;
    }

    public function props(array $props)
    {
        $this->props = $props;

        return $this;
    }

    public function toArray()
    {
        return [
            'component' => $this->component,
            'props'     => $this->props,
        ];
    }
}