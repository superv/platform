<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Negotiator\Negotiator;

class Builder
{
    /** @var \SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract */
    protected $action;

    protected $contexts = [];

    public function __construct($actionClass)
    {
        $this->addContext($this->action = $actionClass::make());
    }

    public function compose(): array
    {
        (new Negotiator)->handshake($this->contexts);

        return $this->action->compose();
    }

    public function addContext($context)
    {
        $this->contexts[] = $context;

        return $this;
    }
}