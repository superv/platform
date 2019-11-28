<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

class FieldConfig
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }
}