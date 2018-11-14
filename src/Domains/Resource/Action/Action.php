<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class Action
{
    /**
     * Unique name of the action
     *
     * @var string
     */
    protected $name;

    /** @var string */
    protected $title;

    public function compose(ResourceEntry $entry): array
    {
        return [
            'name'  => $this->getName(),
            'title' => $this->getTitle(),
            'url'   => $entry->route($this->getName()),
        ];
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