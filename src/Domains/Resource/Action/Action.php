<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Exceptions\PlatformException;

class Action
{
    /**
     * @var string
     */
    protected $name;

    /** @var string */
    protected $title;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    protected $built = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function copy()
    {
        return clone $this;
    }

    public function build(): self
    {
        $this->built = true;

        return $this;
    }

    public function compose(ResourceEntry $entry): array
    {
        if (! $this->isBuilt()) {
            throw new PlatformException('Action is not built yet');
        }

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

    public function isBuilt(): bool
    {
        return $this->built;
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