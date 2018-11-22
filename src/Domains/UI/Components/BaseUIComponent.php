<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composition;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

abstract class BaseUIComponent implements UIComponent, Composable
{
    use FiresCallbacks;

    protected $props = [];

    protected $classes = [];

    abstract public function getName(): string;

    abstract public function getProps(): array;

    public function addClass(string $class)
    {
        $this->classes[] = $class;

        return $this;
    }

    public function compose(array $params = [])
    {
        $composition = new Composition([
            'component' => $this->getName(),
            'uuid'      => $this->uuid(),
            'props'     => $this->getProps(),
            'class'     => $this->getClasses(),
        ]);

        $this->fire('composed', ['composition' => $composition]);

        return $composition->toArray();
    }

    public function getHandle(): string
    {
        return 'cmp';
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public static function make(): self
    {
        return new static;
    }
}