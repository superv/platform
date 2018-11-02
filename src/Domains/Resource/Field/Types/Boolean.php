<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\ResourceEntryModel;

class Boolean extends FieldType
{
    protected $type = 'boolean';

    public function getMutator()
    {
        return function(ResourceEntryModel $entry, $value) {
            $value = ($value === 'false' || !$value) ? false : true;
            $entry->setAttribute($this->getName(), $value);
         };
    }
}