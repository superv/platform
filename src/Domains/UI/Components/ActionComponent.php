<?php

namespace SuperV\Platform\Domains\UI\Components;


use SuperV\Platform\Domains\Resource\Action\Action;

class ActionComponent extends BaseComponent
{
    protected $name = 'sv-action';

    /** @var Action */
    protected $action;

    public function getProps(): Props
    {
        return $this->props->merge($this->action->compose()->get());
    }

    public function uuid()
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