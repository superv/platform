<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;

class Boolean extends FieldType
{
    public function getAccessor(): ?Closure
    {
        return function ($value) {
            return ($value === 'false' || ! $value) ? false : true;
        };
    }

    public function getMutator(): ?Closure
    {
        return $this->getAccessor();
    }
}