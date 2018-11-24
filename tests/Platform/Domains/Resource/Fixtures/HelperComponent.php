<?php

namespace Tests\Platform\Domains\Resource\Fixtures;

use SuperV\Platform\Domains\UI\Components\BaseUIComponent;

class HelperComponent extends BaseUIComponent
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

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function from($array)
    {
        $component = new self($array['props']);
        $component->name = $array['component'];
        $component->uuid = $array['uuid'];

        return $component;
    }
}