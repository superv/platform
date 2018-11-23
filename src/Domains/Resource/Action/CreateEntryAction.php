<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Composition;

class CreateEntryAction extends Action
{
    protected $name = 'create';

    protected $title = 'Create';

    protected $routeUrl;

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', 'sv/res/{res.handle}/create');
    }
}