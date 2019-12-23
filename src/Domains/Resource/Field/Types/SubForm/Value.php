<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\SubForm;

use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;
use SuperV\Platform\Domains\Resource\Field\FieldValue;

class Value extends FieldValue
{
    public function resolve(): FieldValueInterface
    {
        if ($this->entry) {
            $this->field->getFieldType()->setEntryId($this->entry->getAttribute($this->field->getColumnName()));
        }

        if ($this->request) {
            $all = $this->request->all();

            $this->field->getFieldType()->setFormData(array_get($all, $this->field->getHandle()));
        }

        return parent::resolve();
    }
}