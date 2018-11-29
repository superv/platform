<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Filter;

interface Filter
{
    public function getIdentifier();

    public function getType();

    public function getPlaceholder();

    public function apply($query, $value);
}