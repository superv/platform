<?php

namespace SuperV\Platform\Domains\Resource\Model\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class EntrySavingEvent
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry
     */
    public $entry;

    /**
     * @var Form
     */
    public $form;

    public function __construct(ResourceEntry $entry, array $params = [])
    {
        $this->entry = $entry;
        $this->form = $params['form'] ?? null;
    }
}