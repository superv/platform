<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\PageRenderedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\PageResolvedHook;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Domains\UI\Page\ResourcePage;

class CategoriesDashboardPage implements PageResolvedHook, PageRenderedHook
{
    public static $identifier = 'testing.categories.pages:dashboard';

    public function resolved(ResourcePage $page, \SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $_SERVER['__hooks::pages.dashboard.resolved'] = compact('page', 'resource');
    }

    public function rendered(Page $page, \SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $_SERVER['__hooks::pages.dashboard.rendered'] = compact('page', 'resource');

        $_SERVER['__hooks::pages.dashboard.rendered'] = [
            'page'     => $page,
            'resource' => $resource,
            'built'    => $page->isBuilt(),
        ];
    }
}
