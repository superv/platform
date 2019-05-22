<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use Closure;

interface HasPresenter
{
    public function getPresenter(): Closure;
}