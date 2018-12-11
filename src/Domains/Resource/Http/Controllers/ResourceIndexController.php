<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Action\CreateEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Contracts\RequiresEntry;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceIndexController extends BaseApiController
{
    use ResolvesResource;

    public function page()
    {
        $resource = $this->resolveResource();

        $page = Page::make($resource->getLabel());
        $page->addBlock(sv_loader($resource->route('index.table')));
        $page->addAction(CreateEntryAction::make('New '.$resource->getSingularLabel()));

        if ($callback = $resource->getCallback('index.page')) {
            app()->call($callback, ['page' => $page]);
        }

        return $page->build(['res' => $resource->toArray()]);
    }

    public function action()
    {
        $resource = $this->resolveResource();

        if (! $action = $resource->getAction($this->route->parameter('action'))) {
            PlatformException::fail('Resource action not found: '.$action);
        }

        if ($action instanceof RequiresEntry) {
            $action->setEntry($this->entry);
        }

        if ($action instanceof HandlesRequests) {
            return $action->handleRequest($this->request);
        }

        return 'no response from action';
    }

    public function table()
    {
        $table = ($resource = $this->resolveResource())->resolveTable();

        if ($this->route->parameter('data')) {
            return $table->setRequest($this->request)->build();
        }

        if ($callback = $resource->getCallback('index.config')) {
            app()->call($callback, ['table' => $table]);
        }

        return MakeComponentTree::dispatch($table)->withTokens(['res' => $resource->toArray()]);
    }

    public function tableAction()
    {
        $action = $this->resolveTableAction();

        if ($action instanceof HandlesRequests) {
            $action->handleRequest($this->request);
        }

        return $this->resolveTableAction();
    }

    public function tableActionPost()
    {
        return $this->resolveTableAction()->handleRequest($this->request);
    }
}