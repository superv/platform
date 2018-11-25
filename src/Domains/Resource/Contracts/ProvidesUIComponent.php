<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\UI\Components\ComponentContract;

interface ProvidesUIComponent
{
    public function makeComponent(): ComponentContract;
}