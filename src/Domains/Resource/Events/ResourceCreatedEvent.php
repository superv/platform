<?php

namespace SuperV\Platform\Domains\Resource\Events;

use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Support\Fireable;

class ResourceCreatedEvent
{
    use Fireable;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceModel
     */
    public $resourceEntry;

    public function __construct(ResourceModel $resourceEntry)
    {
        $this->resourceEntry = $resourceEntry;
    }
}
