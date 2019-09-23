<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\ResourceConfig;

interface ConfigResolvedHook
{
    public function configResolved(ResourceConfig $config);
}
