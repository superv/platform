<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Domains\UI\Components\ActionComponent;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composition;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Action implements ActionContract, Composable
{
    use FiresCallbacks;

    /**
     * Unique name of the action
     *
     * @var string
     */
    protected $name;

    /** @var string */
    protected $title;

    protected $uuid;

    protected function __construct()
    {
        $this->boot();
    }

    protected function boot()
    {
        $this->uuid = uuid();
    }

    public function makeComponent() {
        return ActionComponent::from($this);
    }


    public function compose(array $params = [])
    {
        $composition = new Composition([
            'name'  => $this->getName(),
            'title' => $this->getTitle(),
        ]);

        $this->fire('composed', ['composition' => $composition]);

        return $composition;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title ?? ucwords($this->name);
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function make(?string $name = null): self
    {
        $action = new static;
        if ($name) {
            $action->name = $name;
        }

        return $action;
    }
}