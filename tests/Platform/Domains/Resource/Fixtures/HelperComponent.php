<?php

namespace Tests\Platform\Domains\Resource\Fixtures;

use SuperV\Platform\Domains\UI\Components\BaseComponent;

class HelperComponent extends BaseComponent
{
    protected $name;

    protected $uuid;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public function countProp($key)
    {
        if ($prop = $this->getProp($key)) {
            return count($prop);
        }

        return 0;
    }

    public static function from($array)
    {
        $component = new self($array['props']);
        $component->name = $array['component'];
        $component->uuid = $array['uuid'] ?? null;

        return $component;
    }
}