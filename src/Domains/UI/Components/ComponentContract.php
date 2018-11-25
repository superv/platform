<?php

namespace SuperV\Platform\Domains\UI\Components;

interface ComponentContract
{
    public function uuid();

    public function getProps(): Props;

    public function addClass(string $class);
}