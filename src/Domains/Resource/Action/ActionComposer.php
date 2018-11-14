<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Support\Negotiator\Negotiator;

class ActionComposer
{
    /** @var \SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract */
    protected $action;

    protected $contexts = [];

    public function __construct(ActionContract $action)
    {
        $this->action = $action;

        $this->addContext($action);
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