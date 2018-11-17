<?php

namespace SuperV\Platform\Domains\UI\Page;

use SuperV\Platform\Domains\UI\Components\PageComponent;

class Page
{
    protected $uuid;

    protected $meta = [];

    protected $blocks = [];

    protected $actions = [];

    protected function __construct(string $title)
    {
        $this->boot();
        $this->meta['title'] = $title;
    }

    protected function boot()
    {
        $this->uuid = uuid();
    }

    public function addBlock($block)
    {
        $this->blocks[] = $block;

        return $this;
    }

    public function addBlocks(array $blocks = [])
    {
        $this->blocks = array_merge($this->blocks, $blocks);

        return $this;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function makeComponent(): PageComponent
    {
        return PageComponent::from($this);
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta($key, $value)
    {
        array_set($this->meta, $key, $value);

        return $this;
    }

    public function getUrl()
    {
        return 'sv/pag/'.$this->uuid();
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): Page
    {
        $this->actions = $actions;

        return $this;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function make(string $title)
    {
        return new static($title);
    }
}