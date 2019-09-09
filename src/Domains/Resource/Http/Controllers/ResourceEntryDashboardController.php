<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

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
        $page->setParent(['title' => $resource->getLabel(), 'url' => $resource->route('dashboard')]);
        $page->setSelectedSection($this->route->parameter('section'));
        $page->setDefaultSection('view');

        if ($callback = $resource->getCallback('view.page')) {
            app()->call($callback, ['page' => $page, 'entry' => $this->entry]);
        }

        $page->addBlock(Component::make('sv-router-portal')->setProps([
            'name' => $resource->getHandle().':'.$this->entry->getId(),
        ]));

        $page->addSection([
            'identifier' => 'view',
            'title'      => 'View',
            'url'        => $resource->route('entry.view', $this->entry),
            'target'     => 'portal:'.$resource->getHandle().':'.$this->entry->getId(),
        ]);

        $page->addSection([
            'identifier' => 'edit',
            'title'      => 'Edit',
            'url'        => $resource->route('forms.edit', $this->entry),
            'target'     => 'portal:'.$resource->getHandle().':'.$this->entry->getId(),
        ]);

        $page->setMeta('url', 'sv/res/'.$resource->getHandle().'/'.$this->entry->getId());

        return $page->build(['res' => $resource->toArray(), 'entry' => $this->entry]);
    }
}
