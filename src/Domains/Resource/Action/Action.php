<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Support\Composition;
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

    protected function __construct()
    {
        $this->boot();
    }

    protected function boot()
    {
    }

    public function compose(): Composition
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

    public static function make(?string $name = null): self
    {
        $action = new static;
        if ($name) {
            $action->name = $name;
        }

        return $action;
    }
}