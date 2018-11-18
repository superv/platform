<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Contracts\Bootable;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Support\Composition;

class PageComponent extends BaseUIComponent implements Bootable
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

    public function boot()
    {
        $this->on('composed', function(Composition $composition) {
           $composition->replace('class', ['w-full']) ;
        });
    }

    public static function from(Page $page): self
    {
        $static = new static;
        $static->page = $page;

        return $static;
    }
}