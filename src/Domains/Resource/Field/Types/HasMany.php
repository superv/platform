<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Field\FieldType;

class HasMany extends FieldType
{
    protected $hasColumn = false;

    public function show(): bool
    {
        return false;
    }
}