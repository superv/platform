<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Boolean;

use SuperV\Platform\Domains\Resource\Field\FieldValue;

class Value extends FieldValue
{
    public function get()
    {
        return ($this->value === 'false' || ! $this->value) ? false : true;
    }
}