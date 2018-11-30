<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Resource;

interface RequiresResource
{
    public function setResource(Resource $resource);
}