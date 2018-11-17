<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Contracts\Hibernatable;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composition;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HibernatableConcern;

abstract class BaseUIComponent implements UIComponent, Composable, Hibernatable
{
    use FiresCallbacks;
    use HibernatableConcern;

    protected $props = [];

    abstract public function getName(): string;

    abstract public function getProps(): array;

    public function compose(array $params = [])
    {
        $composition = new Composition([
            'component' => $this->getName(),
            'uuid'      => $this->uuid(),
            'props'     => $this->getProps(),
        ]);

        return $composition->toArray();
    }

    public function getHandle():string
    {
        return 'cmp';
    }

    public static function make(): self
    {
        return new static;
    }
}