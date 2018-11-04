<?php

namespace SuperV\Platform\Domains\Resource\Visibility;

class Visibility
{
    protected $hide = [];

    public function hideIf()
    {
        $this->hide[] = $condition = new Condition($this);

        return $condition;
    }
}