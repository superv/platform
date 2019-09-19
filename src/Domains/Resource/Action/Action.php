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
     * Unique name of the action
     *
     * @var string
     */
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
            'name'  => $this->getName(),
            'type'  => $this->getType(),
            'target' => $this->getTarget(),
            'title' => $this->getTitle(),
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
        return $this->title ?? ucwords($this->name);
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

    public function uuid(): string
    {
        return $this->uuid;
    }

    /** @return static */
    public static function make(?string $title = null, ?string $name = null)
    {
//        if ($title && ! $name) {
//            $name = str_slug($title, '_');
//        }

        return new static(array_filter(compact('title', 'name')));
    }
}
