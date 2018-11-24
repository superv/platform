<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Contracts\AcceptsResource;
use SuperV\Platform\Domains\Resource\Resource;

abstract class ResourceAction extends Action implements AcceptsResource
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function acceptResource(Resource $resource)
    {
        $this->resource = $resource;
    }
}