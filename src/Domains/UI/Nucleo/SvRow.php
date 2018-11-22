<?php

namespace SuperV\Platform\Domains\UI\Nucleo;

class SvRow extends SvComponent
{
    protected $name = 'sv-row';

    public function column($block)
    {
        $this->props['columns'][] = $block;

        return $this;
    }
}