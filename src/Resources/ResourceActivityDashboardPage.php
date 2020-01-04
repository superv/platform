<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\PageResolvedHook;
use SuperV\Platform\Domains\UI\Page\ResourcePage;

class ResourceActivityDashboardPage implements PageResolvedHook
{
    public static $identifier = 'sv.platform.activities.pages:dashboard';

    public function resolved(ResourcePage $page, \SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $page->notCreatable();

//        $fields = $resource->indexFields();
//        $fields->show('entry');
//        $fields->show('user')->copyToFilters();
//        $fields->show('resource')->copyToFilters();
//
//        $resource->searchable(['email']);
    }
}
