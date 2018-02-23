<?php

namespace SuperV\Platform\Contracts\Entry;

interface PropertyMutator
{
    public function mutate($object, $property, $value);
}