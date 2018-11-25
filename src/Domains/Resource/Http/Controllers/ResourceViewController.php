<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceViewController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $resource = $this->resolveResource();

        $page = Page::make('View '.$resource->getEntryLabel($this->entry));

        $view = $resource->resolveView($this->entry);
        $page->setMeta('header', false);
        $page->addBlock($view->getHeading());

        return $page->build();
    }
}