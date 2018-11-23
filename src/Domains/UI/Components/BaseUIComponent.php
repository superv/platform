<?php

namespace SuperV\Platform\Domains\UI\Components;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Domains\UI\Components\Concerns\StyleHelper;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Composition;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

abstract class BaseUIComponent implements UIComponent, Composable
{
    use FiresCallbacks;
    use StyleHelper;

    protected $props;

    protected $classes = [];


    abstract public function getName(): string;

    public function __construct(array $props = [])
    {
        $this->props = new Props($props);
    }

    public function getProps(): Props
    {
        return $this->props;
    }

    public function getProp($key)
    {
        return $this->props->get($key);
    }

    public function addClass(string $class)
    {
        $this->classes[] = $class;

        return $this;
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
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