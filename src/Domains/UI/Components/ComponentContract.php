<?php

namespace SuperV\Platform\Domains\UI\Components;

interface ComponentContract
{
    public function uuid();

    public function getName(): string;

    public function setName(string $name): ComponentContract;

    public function getProps(): Props;

    public function addClass(string $class): ComponentContract;

    public function withTokens(array $tokens): ComponentContract;

    public function setProp($key, $value): ComponentContract;

    public function setProps($props): ComponentContract;
}