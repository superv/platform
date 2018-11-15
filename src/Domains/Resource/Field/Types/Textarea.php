<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;

class Textarea extends FieldType implements NeedsDatabaseColumn
{
    public function getConfig(): array
    {
        return array_merge(['hide.table' => true], parent::getConfig());
    }
}