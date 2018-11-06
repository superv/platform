<?php

namespace SuperV\Platform\Domains\Resource\Model\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;

class EntrySavingEvent
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntryModel
     */
    public $entry;

    public function __construct(ResourceEntryModel $entry)
    {
        $this->entry = $entry;
    }
}