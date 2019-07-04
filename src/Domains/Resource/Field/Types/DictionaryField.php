<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class DictionaryField extends FieldType implements RequiresDbColumn, HasAccessor, HasModifier
{
    protected function boot()
    {
        $this->field->on('form.accessing', $this->getAccessor());
        $this->field->on('form.mutating', $this->getModifier());
        $this->field->on('view.presenting', $this->getAccessor());
        $this->field->on('table.presenting', $this->getAccessor());
    }

    public function getAccessor(): Closure
    {
        return function ($value) {

            if (is_string($value)) {

                return json_decode($value, true);
            } else {
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
////            dump($value);
////            if (!is_string($value)) {
////                return $entry->setAttribute($this->getColumnName(),json_encode($value));
////            }
//
//            return $value;
//        };
//    }
}