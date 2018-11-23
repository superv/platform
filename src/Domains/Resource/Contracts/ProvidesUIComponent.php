<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\UI\Components\UIComponent;

interface ProvidesUIComponent
{
    public function makeComponent(): UIComponent;
}