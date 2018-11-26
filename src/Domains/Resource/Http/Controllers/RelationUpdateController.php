<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationUpdateController extends BaseApiController
{
    use ResolvesResource;

    public function edit()
    {
        return $this->resolveRelation()
                    ->makeForm()
                    ->setUrl(str_replace_last('/edit', '', url()->current()))
                    ->makeComponent();
    }
}