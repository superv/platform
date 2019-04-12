<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use Closure;

interface HasAccessor
{
    public function getAccessor(): Closure;
}