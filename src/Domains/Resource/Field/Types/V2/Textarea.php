<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\V2;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Field\Types\FieldTypeV2;

class Textarea extends FieldTypeV2 implements NeedsDatabaseColumn
{
    public function mergeConfig()
    {
        return ['hide.table' => true];
    }
}