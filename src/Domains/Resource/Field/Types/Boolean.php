<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Resource\Field\Field;

class Boolean extends Field
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