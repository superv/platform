<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Requirements;

use SuperV\Platform\Domains\Resource\Resource;

interface AcceptsResource
{
    public function acceptResource(Resource $resource);
}