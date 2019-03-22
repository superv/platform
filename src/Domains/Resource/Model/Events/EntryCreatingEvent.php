<?php

namespace SuperV\Platform\Domains\Resource\Model\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

class EntryCreatingEvent
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    public $entry;

    public function __construct(EntryContract $entry)
    {
        $this->entry = $entry;
    }
}