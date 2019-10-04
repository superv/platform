<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers\Dashboard;

use Event;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Page\EntryPage;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceEntryDashboardController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $resource = $this->resolveResource();

        $page = EntryPage::make($resource->getEntryLabel($this->entry));
        $page->setResource($resource);
        $page->setEntry($this->entry);
        $page->setParent(['title' => $resource->getLabel(), 'url' => $resource->router()->dashboardSPA()]);
        $page->setSelectedSection($this->route->parameter('section'));
        $page->setDefaultSection('view');

        Event::dispatch($resource->getIdentifier().'.pages:entry_dashboard.events:resolved', compact('page', 'resource'));

        if ($callback = $resource->getCallback('entry.dashboard')) {
            app()->call($callback, ['page' => $page, 'entry' => $this->entry]);
        }

        $page->addBlock(Component::make('sv-router-portal')->setProps([
            'name' => $resource->getIdentifier().':'.$this->entry->getId(),
        ]));

        $page->addSection([
            'identifier' => 'view',
            'title'      => 'View',
            'url'        => $this->entry->router()->view(),
            'target'     => 'portal:'.$resource->getIdentifier().':'.$this->entry->getId(),
        ]);

        $page->addSection([
            'identifier' => 'edit',
            'title'      => 'Edit',
            'url'        => $this->entry->router()->updateForm(),
            'target'     => 'portal:'.$resource->getIdentifier().':'.$this->entry->getId(),
        ]);

        $page->setMeta('url', 'sv/res/'.$resource->getIdentifier().'/'.$this->entry->getId());

        $page = $page->build(['res' => $resource->toArray(), 'entry' => $this->entry]);

        Event::dispatch($resource->getIdentifier().'.pages:entry_dashboard.events:rendered', compact('page', 'resource'));

        return $page;
    }
}
