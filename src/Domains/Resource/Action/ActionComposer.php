<?php

namespace SuperV\Platform\Domains\Resource\Action;

use ReflectionClass;
use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Support\Negotiator\Negotiator;
use SuperV\Platform\Support\Negotiator\Provider;
use Tests\Platform\Domains\Resource\Action\RequiresActionTestEntry;

class ActionComposer implements Provider
{
    protected $entry;

    protected $table;

    /** @var \SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract */
    protected $action;

    protected $resolvers = [
        RequiresActionTestEntry::class => 'entry',
    ];

    public function __construct(ActionContract $action)
    {
        $this->action = new $action;
    }

    public function build()
    {
        (new Negotiator($this->action))($this);
    }


    public function getResolutionFor($requirement)
    {
        return function ($requiree) use ($requirement) {
            $resolver = $this->resolvers[$requirement];
            $requiredValue = $this->{$resolver};

            $reflection = new ReflectionClass($requirement);

            $method = $reflection->getMethods()[0]->getName();

            $requiree->{$method}($requiredValue);
        };
    }

    public function getProvidings(): array
    {
        return array_keys($this->resolvers);
    }

    public function compose(): array
    {
        $this->build();

        return array_merge([
                    'name'  => $this->action->getName(),
                    'title' => $this->action->getTitle(),
                ], $this->action->getComposed());
    }

    public function setEntry($entry)
    {
        $this->entry = $entry;

        return $this;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}