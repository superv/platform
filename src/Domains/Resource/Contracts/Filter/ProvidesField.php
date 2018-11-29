<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Filter;

use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

interface ProvidesField
{
    public function makeField(): Field;
}