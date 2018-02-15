<?php

namespace SuperV\Platform\Contracts\Entry;

interface PropertyAccessor
{
    public function access($object, $property);
}