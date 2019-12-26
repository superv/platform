<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use Current;
use SuperV\Platform\Domains\Resource\Action\DeleteEntryAction;
use SuperV\Platform\Domains\Resource\Action\EditEntryAction;
use SuperV\Platform\Domains\Resource\Action\ViewEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Contracts\RequiresEntry;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ListController extends BaseApiController
{
    use ResolvesResource;

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
        $resource = $this->resolveResource();

        Current::user()
               ->canOrFail($resource->getChildIdentifier('actions', 'list'));

        $table = $resource->resolveTable();

        if ($this->route->parameter('data')) {
            return $table->setRequest($this->request)->build();
        }

        if (empty($table->getRowActions())) {
            if ($table->isDeletable()) {
                $table->addRowAction(DeleteEntryAction::make($resource->getChildIdentifier('actions', 'delete')));
            }

            if ($table->isViewable()) {
                $table->addRowAction(ViewEntryAction::make($resource->getChildIdentifier('actions', 'view')));
            }
            if ($table->isEditable()) {
                $table->addRowAction(EditEntryAction::make($resource->getChildIdentifier('actions', 'edit')));
            }
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

    public function postTableAction()
    {
        return $this->resolveTableAction()->handleRequest($this->request);
    }
}
