<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Domains\UI\Page\Page;

class PageComponent extends BaseUIComponent
{
    protected $name = 'sv-page';

    /** @var Page */
    protected $page;

    public function getName(): string
    {
        return $this->name;
    }

    public function getProps(): array
    {
        return [
            'meta'    => $this->page->getMeta(),
            'actions' => $this->page->getActions(),
            'blocks'  => $this->page->getBlocks(),
        ];
    }

    public function uuid(): string
    {
        return $this->page->uuid();
    }

    public static function from(Page $page): self
    {
        $static = new static;
        $static->page = $page;

        return $static;
    }
}