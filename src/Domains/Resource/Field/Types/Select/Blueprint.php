<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Select;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;

class Blueprint extends FieldBlueprint
{
    public function options(array $options)
    {
        $this->setConfigValue('options', $options);

        return $this;
    }
}