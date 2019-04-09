<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Field;

class ArrayField extends Field
{
    protected function boot()
    {
        $this->on('form.accessing', $this->accessor());
        $this->on('form.mutating', $this->mutator());
        $this->on('view.presenting', $this->accessor());
        $this->on('table.presenting', $this->accessor());
    }

    protected function accessor()
    {
        return function ($value) {
            if (is_string($value)) {
                return json_decode($value, true);
            }

            return $value;
        };
    }

    protected function mutator()
    {
        return function ($value, EntryContract $entry) {
//            dump($value);
//            if (!is_string($value)) {
//                return $entry->setAttribute($this->getColumnName(),json_encode($value));
//            }

            return $value;
        };
    }
}