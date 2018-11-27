<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;

class Boolean extends FieldType implements NeedsDatabaseColumn
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