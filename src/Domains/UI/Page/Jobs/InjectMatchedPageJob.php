<?php

namespace SuperV\Platform\Domains\UI\Page\Jobs;

use Illuminate\Routing\Route;
use SuperV\Platform\Domains\UI\Page\Page;
use Illuminate\Routing\Events\RouteMatched;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\View\ViewTemplate;
use SuperV\Platform\Domains\UI\Page\PageCollection;
use SuperV\Platform\Domains\UI\Page\Features\MakePageButtons;

/**
 * Class InjectMatchedPageJob.
 *
 * Gets page from route,
 * Finds the entry of the page model, sets the instance on Page (used in parsing buttons)
 * Makes page buttons
 * Sets page in the view template
 */
class InjectMatchedPageJob extends Feature
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
        if (! $route = $event->route) {
            return;
        }

        /** @var Page $page */
        if (! $page = $this->pages->get($route->getName())) {
            return;
        }

        // page entry
        if ($entryId = $route->parameter($page->getManifest()->getRouteKeyName())) {
            if ($model = $page->getModel()) {
                if ($entry = $model::find($entryId)) {
                    $page->setEntry($entry);
                }
            }
        }

        $this->serve(new MakePageButtons($page));

        app(ViewTemplate::class)->set('page', $page);
    }
}
