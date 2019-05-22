<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use Closure;

interface HasModifier
{
    public function getModifier(): Closure;
}