<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Payload;

class CreateEntryAction extends Action
{
    protected $name = 'create';

    protected $title = 'Create';

    protected $routeUrl;

    public function onComposed(Payload $payload)
    {
        $payload->set('url', 'sv/api/res/{res.handle}/create');
        $payload->set('button.color', 'green');
    }
}