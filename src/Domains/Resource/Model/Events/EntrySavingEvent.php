<?php

namespace SuperV\Platform\Domains\Resource\Model\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;

class EntrySavingEvent
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntryModel
     */
    public $entry;

    /**
     * @var Form
     */
    public $form;

    public function __construct(ResourceEntryModel $entry, array $params = [])
    {
        $this->entry = $entry;
        $this->form = $params['form'] ?? null;
    }
}