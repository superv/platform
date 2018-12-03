<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Action\CreateEntryAction;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceIndexController extends BaseApiController
{
    use ResolvesResource;

    public function page()
    {
        $resource = $this->resolveResource();

        $page = Page::make($resource->getLabel());
        $page->addBlock(sv_loader($resource->route('index.table')));
        $page->addAction(CreateEntryAction::make());

        return $page->build(['res' => $resource->toArray()]);
    }

    public function table()
    {
        $resource = $this->resolveResource();
        $table = $resource->resolveTable();

        if ($this->route->parameter('data')) {
            return $table->setRequest($this->request)->build();
        }

        return MakeComponentTree::dispatch($table)->withTokens(['res' => $resource->toArray()]);
    }

}