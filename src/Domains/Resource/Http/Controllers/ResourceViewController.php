<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceViewController extends BaseApiController
{
    use ResolvesResource;

    public function view()
    {
        $resource = $this->resolveResource();

        return MakeComponentTree::dispatch($resource->resolveView($this->entry));
    }
}