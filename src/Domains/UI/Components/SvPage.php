<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Support\Concerns\Hydratable;

class SvPage
{
    use Hydratable;

    protected $uuid;

    protected $name;

    protected $title;

    protected $blocks = [];

    protected function __construct()
    {
        $this->uuid = uuid();

        $this->boot();
    }

    public function addBlock($block): self
    {
        $this->blocks[] = $block;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    protected function boot()
    {
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function make(string $name, ?string $title = null): self
    {
        return (new static)->hydrate(compact('name', 'title'));
    }
}