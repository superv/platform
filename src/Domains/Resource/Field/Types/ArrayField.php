<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class ArrayField extends FieldType implements  HasAccessor, HasModifier
{
//    protected function boot()
//    {
//        $this->field->on('form.accessing', $this->accessor());
//        $this->field->on('form.mutating', $this->mutator());
//        $this->field->on('view.presenting', $this->accessor());
//        $this->field->on('table.presenting', $this->accessor());
//    }
//
//    protected function accessor()
//    {
//        return function ($value) {
//            if (is_string($value)) {
//                return json_decode($value, true);
//            }
//
//            return $value;
//        };
//    }
//
//    protected function mutator()
//    {
//        return function ($value, EntryContract $entry) {
//            return $value;
//        };
//    }

    public function getAccessor(): Closure
    {
        return function ($value) {
            if (is_string($value)) {
                return json_decode($value, true);
            }

            return $value;
        };
    }

    public function getModifier(): Closure
    {
        return function ($value, EntryContract $entry) {
            return $value;
        };
    }
}