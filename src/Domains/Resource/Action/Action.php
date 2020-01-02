<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\UI\Components\ActionComponent;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\Hydratable;

class Action implements ActionContract, Composable, ProvidesUIComponent
{
    use FiresCallbacks;
    use Hydratable;

    /**
     * Unique identifier of the action
     *
     * @var string
     */
    protected $identifier;

    /** @var string */
    protected $name;

    /** @var string */
    protected $title;

    protected $uuid;

    /**
     * Action type [redirect|other]
     *
     * @var string
     */
    protected $type;

    protected $target;

    protected function __construct(array $params = [])
    {
        $this->uuid = uuid();
        $this->hydrate($params);
    }

    public function makeComponent(): ComponentContract
    {
        return ActionComponent::from($this);
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        $payload = new Payload([
            'name'   => $this->getName(),
            'type'   => $this->getType(),
            'target' => $this->getTarget(),
            'title'  => $this->getTitle(),
        ]);

        $this->fire('composed', ['payload' => $payload]);

        return $payload;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return sv_trans($this->title ?? ucwords($this->name));
    }

    public function setTitle($title): ActionContract
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    public function setIdentifier(string $identifier): Action
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function uuid(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return static
     */
    public static function make(string $identifier)
    {
        $instance = new static;
//        $instance->uuid = uuid();
        $instance->hydrate(array_filter(compact('identifier')));

        return $instance;
//        return new static(array_filter(compact('identifier')));
    }
}
