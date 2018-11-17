<?php

namespace SuperV\Platform\Domains\UI\Components;


use SuperV\Platform\Domains\Resource\Action\Action;

class ActionComponent extends BaseUIComponent
{
    protected $name = 'sv-action';

    /** @var Action */
    protected $action;

    public function getName(): string
    {
        return $this->name;
    }

    public function getProps(): array
    {
        return $this->action->compose()->get();
    }

    public function uuid(): string
    {
        return $this->action->uuid();
    }

    public static function from(Action $action): self
    {
        $static = new static;
        $static->action = $action;

        return $static;
    }
}