<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

class Text extends FieldType
{
    public function makeRules(): array
    {
        if ($length = $this->getConfigValue('length')) {
            return array_merge(["max:{$length}"], parent::makeRules());
        }

        return parent::makeRules();
    }
}