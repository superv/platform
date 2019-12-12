<?php

namespace SuperV\Platform\Domains\Resource\Builder;

class FieldConfig
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }
}