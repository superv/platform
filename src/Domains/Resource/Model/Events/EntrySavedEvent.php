<?php

namespace SuperV\Platform\Domains\Resource\Model\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class EntrySavedEvent
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry
     */
    public $entry;

    public function __construct(ResourceEntry $entry)
    {
        $this->entry = $entry;
    }
}