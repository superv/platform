<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

class HasMany extends FieldType
{
    public function show(): bool
    {
        return false;
    }
}