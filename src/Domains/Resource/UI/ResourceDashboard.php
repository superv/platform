<?php

namespace SuperV\Platform\Domains\Resource\UI;

use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Page\ResourcePage;

class ResourceDashboard
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function render($section = null)
    {
        $resource = $this->resource;

        $page = ResourcePage::make(__($resource->getLabel()));
        $page->setResource($resource);
        $page->setDefaultSection('all');
        $page->setSelectedSection($section);

        if ($callback = $resource->getCallback('index.page')) {
            app()->call($callback, ['page' => $page]);
        }

        $page->addBlock(Component::make('sv-router-portal')->setProps([
            'name' => $resource->getHandle(),
        ]));

        $page->addSection([
            'identifier' => 'all',
            'title'      => 'All',
            'url'        => $resource->route('dashboard', null, ['section' => 'table']),
            'target'     => 'portal:'.$resource->getHandle(),
            'default'    => ! $section || $section === 'all',
        ]);

        $page->addSection([
            'identifier' => 'create',
            'title'      => 'Create',
            'url'        => $resource->route('forms.create'),
            'target'     => 'portal:'.$resource->getHandle(),
            'default'    => $section === 'create',
        ]);

//        if ($page->isCreatable() && empty($page->getActions())) {
//            $action = CreateEntryAction::make('New '.$resource->getSingularLabel());
//            $action->setTarget($resource->getHandle());
//            $action->setUrl($resource->route('forms.create'));
//            $page->addAction($action);
//        }

        $page->setMeta('url', 'sv/res/'.$resource->getHandle());

        return $page->build(['res' => $resource->toArray()]);
    }

    /** @return static */
    public static function resolve()
    {
        return new static(...func_get_args());
    }
}
