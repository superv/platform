<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;

class Text extends FieldType implements NeedsDatabaseColumn
{
    public function makeRules()
    {
        if ($length = $this->field->getConfigValue('length')) {
            return ["max:{$length}"];
        }
    }

    protected function boot()
    {
    }
}