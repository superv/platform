<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Filter;

use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;

interface ProvidesField
{
    public function makeField(): FieldInterface;
}