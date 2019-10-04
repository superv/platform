<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers\Dashboard;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\UI\ResourceDashboard;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceDashboardController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $resource = $this->resolveResource();
        $section = $this->route->parameter('section');

        return ResourceDashboard::resolve($resource, $section)
                                ->render();
    }
}
