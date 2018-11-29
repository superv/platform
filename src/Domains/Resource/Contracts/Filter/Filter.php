<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Filter;

interface Filter
{
    public function getName();

    public function apply($query, $value);
}