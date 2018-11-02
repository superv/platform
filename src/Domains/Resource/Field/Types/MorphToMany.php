<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

class MorphToMany extends FieldType
{
    protected $hasColumn = false;

    public function show(): bool
    {
        return false;
    }
}