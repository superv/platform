<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Filter\DistinctFilter;

class Text extends FieldType implements NeedsDatabaseColumn, ProvidesFilter
{
    public function makeRules()
    {
        if ($length = $this->field->getConfigValue('length')) {
            return ["max:{$length}"];
        }
    }

    public function makeFilter(array $params = [])
    {
        return DistinctFilter::make($this->getName());
    }
}