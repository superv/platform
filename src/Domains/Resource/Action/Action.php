<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\UI\Components\ActionComponent;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Action implements ActionContract, Composable, ProvidesUIComponent
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
        $this->uuid = uuid();
    }

    public function makeComponent(): ComponentContract
    {
        return ActionComponent::from($this);
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        $payload = new Payload([
            'name'  => $this->getName(),
            'title' => $this->getTitle(),
        ]);

        $this->fire('composed', ['payload' => $payload]);

        return $payload;
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

    /** @return static */
    public static function make(?string $name = null)
    {
        $action = new static;
        if ($name) {
            $action->name = $name;
        }

        return $action;
    }
}