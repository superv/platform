<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Select;

use SuperV\Platform\Domains\Resource\Blueprint\FieldBlueprint;

class SelectFieldBlueprint extends FieldBlueprint
{
    public function options(array $options)
    {
        $this->setConfigValue('options', $options);

        return $this;
    }
}