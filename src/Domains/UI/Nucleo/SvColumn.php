<?php

namespace SuperV\Platform\Domains\UI\Nucleo;

class SvColumn extends SvComponent
{
    protected $name = 'sv-column';

    public function row($block)
    {
        if (! is_null($block)) {
            $this->props['rows'][] = $block;
        }

        return $this;
    }
}