<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Page\ResourcePage;

interface PageResolvedHook
{
    public function resolved(ResourcePage $page, Resource $resource);
}
