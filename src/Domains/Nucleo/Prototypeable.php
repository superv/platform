<?php

namespace SuperV\Platform\Domains\Nucleo;

trait Prototypeable
{
    public function prototype()
    {
        return Prototype::where('slug', $this->getTable())->first();
    }
}