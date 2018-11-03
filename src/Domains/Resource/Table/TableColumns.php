<?php

namespace SuperV\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Resource;
use Illuminate\Support\Collection;

class TableColumns extends Collection
{
    public function mergeFrom(Resource $resource)
    {
        $fields = $resource->getFields()
                           ->map(function (Field $field) {
                               return $field->build();
                           });

        $this->items = $this->merge($fields)->all();

        return $this;
    }
}