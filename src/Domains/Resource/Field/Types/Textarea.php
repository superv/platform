<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;

class Textarea extends FieldType implements NeedsDatabaseColumn
{
    public function mergeConfig()
    {
        return ['hide.table' => true];
    }
}