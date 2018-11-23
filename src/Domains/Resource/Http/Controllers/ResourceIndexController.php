<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Action\CreateEntryAction;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceIndexController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke(ResourceTable $table)
    {
        $table->setResource($resource = $this->resolveResource());

        if ($this->route->parameter('data')) {
            return $table->build();
        }

        $page = Page::make($resource->getLabel());
        $page->addBlock($table);
        $page->addAction(CreateEntryAction::make());

       return  $page->build(['res' => $resource->toArray()]);
    }
}