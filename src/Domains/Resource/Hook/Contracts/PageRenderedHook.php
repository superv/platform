<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Page\Page;

interface PageRenderedHook
{
    public function rendered(Page $page, Resource $resource);
}
