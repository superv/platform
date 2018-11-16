<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Requirements;

use SuperV\Platform\Domains\Resource\Contracts\Providings\ProvidesRoute;

interface AcceptsRouteProvider
{
    public function acceptRouteProvider(ProvidesRoute $routeProvider);
}