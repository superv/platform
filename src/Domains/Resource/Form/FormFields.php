<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Collection;

class FormFields extends Collection
{
    public function mergeFrom(Resource $resource)
    {
        $fields = $resource->getFields()
                           ->filter(function (FieldType $field) {
                               return $field->show();
                           })
                           ->map(function (FieldType $field) {
                               return $field->build();
                           });

        $this->items = $this->merge($fields)->all();

        return $this;
    }
}