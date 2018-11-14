<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class Action
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $title;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

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

    public static function make(string $name): self
    {
        return new static($name);
    }
}