<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\Contracts\EntryForm as EntryFormContract;
use SuperV\Platform\Domains\Resource\Resource;

class EntryForm extends Form implements EntryFormContract
{
    /** @var Resource */
    protected $resource;

    /** @var EntryContract */
    protected $entry;

    public function getEntry(): ?EntryContract
    {
        return $this->entry;
    }

    public function setEntry(EntryContract $entry): EntryForm
    {
        $this->entry = $entry;

        return $this;
    }

    public function hasEntry(): bool
    {
        return (bool)$this->entry;
    }

    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): EntryForm
    {
        $this->resource = $resource;

        return $this;
    }

    protected function applyExtensionCallbacks(): void
    {
        if ($this->isCreating()) {
            if ($this->resource && $callback = $this->resource->getCallback('creating')) {
                app()->call($callback, ['form' => $this]);
            }
        }

        if ($this->isUpdating()) {
            if ($this->resource && $callback = $this->resource->getCallback('editing')) {
                app()->call($callback, ['form' => $this, 'entry' => $this->getEntry()]);
            }
        }
    }
}
