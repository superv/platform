<?php

namespace SuperV\Platform\Domains\UI\Components;

class ImageComponent extends BaseComponent
{
    protected $name = 'sv-image';

    public function getName(): string
    {
        return $this->name;
    }
}