<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Contracts\Providings\ProvidesRoute;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsRouteProvider;
use SuperV\Platform\Support\Composer\Composition;

class CreateEntryAction extends Action implements AcceptsRouteProvider
{
    protected $name = 'create';

    protected $title = 'Create';

    protected $routeUrl;

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', $this->routeUrl);
    }

    public function acceptRouteProvider(ProvidesRoute $routeProvider)
    {
        $this->routeUrl = $routeProvider->provideRoute('create');
    }
}