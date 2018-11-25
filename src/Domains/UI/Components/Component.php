<?php

namespace SuperV\Platform\Domains\UI\Components;

class Component extends BaseComponent
{
    protected $name;

    public function getName(): string
    {
        return $this->name;
    }
}