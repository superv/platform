<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class Boolean extends FieldType
{
    protected $type = 'boolean';

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