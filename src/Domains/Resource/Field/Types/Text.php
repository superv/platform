<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;

class Text extends FieldType implements NeedsDatabaseColumn
{
    public function makeRules(): array
    {
        if ($length = $this->getConfigValue('length')) {
            return array_merge(["max:{$length}"], parent::makeRules());
        }

        return parent::makeRules();
    }
}