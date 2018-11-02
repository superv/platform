<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Field\FieldType;

class BelongsToMany extends FieldType
{
    protected $hasColumn = false;

    public function show(): bool
    {
        return false;
    }

}