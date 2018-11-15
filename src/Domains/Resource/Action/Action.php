<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Domains\Resource\Contracts\MustBeInitialized;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Action implements ActionContract
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

    protected $payload;

    protected function __construct() { }

    public function compose(): array
    {
        $this->payload = array_filter_null([
            'name'  => $this->getName(),
            'title' => $this->getTitle(),
        ]);

        $this->fire('composed');

        return $this->payload;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title ?? ucwords($this->name);
    }

    public static function make(?string $name = null): self
    {
        $action = new static;
        if ($name) {
            $action->name = $name;
        }

        if ($action instanceof MustBeInitialized) {
            $action->init();
        }

        return $action;
    }
}