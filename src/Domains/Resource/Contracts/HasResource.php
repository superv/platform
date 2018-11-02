<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Resource;

interface HasResource
{
    public function getResource(): ?Resource;

    public function setResource(Resource $resource);
}