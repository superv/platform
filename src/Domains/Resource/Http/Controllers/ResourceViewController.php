<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Domains\UI\Page\ResourcePage;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceViewController extends BaseApiController
{
    use ResolvesResource;

    public function page()
    {
        $resource = $this->resolveResource();

        $page = ResourcePage::make($resource->getLabel());
        $page->setResource($resource);
        $page->setEntry($this->entry);
        $page->addBlock(sv_loader($resource->route('view', $this->entry)));

        if ($callback = $resource->getCallback('view.page')) {
            app()->call($callback, ['page' => $page, 'entry' => $this->entry]);
        }

        return $page->build(['res' => $resource->toArray()]);
    }

    public function view()
    {
        $resource = $this->resolveResource();

        return MakeComponentTree::dispatch($resource->resolveView($this->entry));

    }
}