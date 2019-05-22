<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Filter;

use Closure;
use SuperV\Platform\Domains\Resource\Resource;

interface Filter
{
    public function getIdentifier();

    public function getType();

    public function getLabel();

    public function getPlaceholder();

    public function setApplyCallback(Closure $callback): Filter;

    public function setResource(Resource $resource): Filter;

    public function applyQuery($query, $value);
}