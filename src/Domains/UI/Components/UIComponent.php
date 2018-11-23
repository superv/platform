<?php

namespace SuperV\Platform\Domains\UI\Components;

interface UIComponent
{
    public function uuid(): string;

    public function getProps(): Props;
}