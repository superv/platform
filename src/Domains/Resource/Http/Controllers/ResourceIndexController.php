<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Action\CreateEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Contracts\RequiresEntry;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Domains\UI\Page\ResourcePage;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceIndexController extends BaseApiController
{
    use ResolvesResource;

    public function delete()
    {
        $resource = $this->resolveResource();

        $this->entry->delete();

        return ['data' => ['message' => __(':Entry was deleted', ['Entry' => $resource->getSingularLabel()]) ]];
    }

    public function restore()
    {
        $this->resolveResource();

        $this->entry->restore();
    }

    public function page()
    {
        $resource = $this->resolveResource();

        $page = ResourcePage::make(__($resource->getLabel()));
        $page->setResource($resource);

        if ($callback = $resource->getCallback('index.page')) {
            app()->call($callback, ['page' => $page]);
        }

        $page->addBlock(sv_loader($resource->route('index.table')));

        if ($page->isCreatable() && empty($page->getActions())) {
            $page->addAction(CreateEntryAction::make('New '.$resource->getSingularLabel()));
        }

        return $page->build(['res' => $resource->toArray()]);
    }

    public function action()
    {
        $resource = $this->resolveResource();

        if (! $action = $resource->getAction($this->route->parameter('action'))) {
            PlatformException::fail('Resource action not found: '.$this->route->parameter('action'));
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

        if ($callback = $resource->getCallback('index.config')) {
            app()->call($callback, ['table' => $table, 'fields' => $resource->indexFields()]);
        }

        if ($this->route->parameter('data')) {
            if ($callback = $resource->getCallback('index.data')) {
                app()->call($callback, ['table' => $table, 'fields' => $resource->indexFields()]);
            }

            return $table->setRequest($this->request)->build();
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
