<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Filter\DistinctFilter;

class TextField extends FieldType implements RequiresDbColumn, ProvidesFilter
{
    public function makeRules()
    {
        if ($length = $this->getConfigValue('length')) {
            return ["max:{$length}"];
        }
    }

    public function makeFilter(?array $params = [])
    {
        return DistinctFilter::make($this->getName());
    }
}