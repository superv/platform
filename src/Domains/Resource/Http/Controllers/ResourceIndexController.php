<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Action\CreateEntryAction;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceIndexController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $resource = $this->resolveResource();

        $config = $resource->provideTableConfig();

        if ($this->route->parameter('data')) {
            $table = Table::config($config)->build();


//            dd($table);


            return ['data' => sv_compose($table, ['resource' => ['handle' => $resource->getHandle()]])];
        }

        $page = Page::make($resource->getLabel());
        $page->addBlock($config->makeComponent()->addClass('sv-card')->compose());

        $createAction = CreateEntryAction::make();
        $createAction->acceptRouteProvider($resource);

        $page->setActions([$createAction->makeComponent()]);

        return ['data' => sv_compose($page->makeComponent())];
    }
}