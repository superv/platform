<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceViewController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $resource = $this->resolveResource();

        return MakeComponentTree::dispatch($resource->resolveView($this->entry));

        $page = Page::make('View '.$resource->getEntryLabel($this->entry));

        $page->setMeta('header', false);
        $page->addBlock($resource->resolveView($this->entry));

        return $page->build(['res' => $resource->toArray(), 'entry' => $this->entry->toArray()]);
    }
}