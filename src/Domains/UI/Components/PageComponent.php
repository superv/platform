<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Support\Composer\Payload;

class PageComponent extends BaseComponent
{
    protected $name = 'sv-page';

    protected $uuid;

    public function getName(): string
    {
        return $this->name;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public function onComposed(Payload $payload)
    {
        $payload->set('class', ['w-full']);
    }

    public static function from(Page $page): self
    {
        $static = new static;
        $static->uuid = $page->uuid();
        $static->props->merge([
            'meta'     => $page->getMeta(),
            'actions'  => $page->getActions(),
            'sections' => $page->getSections(),
            'blocks'   => $page->getBlocks(),
        ]);

        return $static;
    }
}
