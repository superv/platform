<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

class Textarea extends FieldType
{
    public function getConfig(): array
    {
        return array_merge(['hide.table' => true], parent::getConfig());
    }

}