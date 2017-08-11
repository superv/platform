<?php namespace SuperV\Platform\Domains\UI\Page\Jobs;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\PageCollection;
use SuperV\Platform\Domains\View\ViewTemplate;

class InjectMatchedPageJob
{
    /**
     * @var PageCollection
     */
    private $pages;

    public function __construct(PageCollection $pages)
    {
        $this->pages = $pages;
    }

    public function handle(RouteMatched $event)
    {
        /** @var Route $route */
        if (!$route = $event->route) {
            return;
        }

        /** @var Page $page */
        if (!$page = $this->pages->get($route->getName())) {
            return;
        }

        superv(ViewTemplate::class)->set('page', $page);
    }
}
