<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers\Dashboard;

use Current;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\UI\ResourceDashboard;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceDashboardController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $resource = $this->resolveResource();

        $canList = Current::user()->can($resource->getChildIdentifier('actions', 'list'));
        $canCreate = Current::user()->can($resource->getChildIdentifier('actions', 'create'));

        if (! $canList && ! $canCreate) {
            abort(403);
        }

        $section = $this->route->parameter('section');

        return ResourceDashboard::resolve($resource, $section)
                                ->render();
    }
}
