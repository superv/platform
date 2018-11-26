<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Action\CreateEntryAction;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableV2;
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

    public function table(TableV2 $table)
    {
        $table->setResource($resource = $this->resolveResource());

        if ($this->route->parameter('data')) {
            return $table->build();
        }

        return $table->makeComponent();
    }

//    public function __invoke2(ResourceTable $table)
//    {
//        $table->setResource($resource = $this->resolveResource());
//
//        if ($this->route->parameter('data')) {
//            return $table->build();
//        }
//
//        $page = Page::make($resource->getLabel());
//        $page->addBlock($table);
//        $page->addAction(CreateEntryAction::make());
//
//        return $page->build(['res' => $resource->toArray()]);
//    }
}