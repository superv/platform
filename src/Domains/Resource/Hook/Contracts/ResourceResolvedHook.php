<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Resource;

interface ResourceResolvedHook
{
    public function resolved(Resource $resource);
}
