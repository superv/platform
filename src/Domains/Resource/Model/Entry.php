<?php

namespace SuperV\Platform\Domains\Resource\Model;

use SuperV\Platform\Domains\Resource\Resource;

class Entry
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntryModel
     */
    protected $entry;

    protected $resourceHandle;

    protected $entryId;

    public function __construct(ResourceEntryModel $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry(): \SuperV\Platform\Domains\Resource\Model\ResourceEntryModel
    {
        return $this->entry;
    }

    public function exists()
    {
        return $this->entry && $this->entry->exists;
    }

    public function __sleep()
    {
        if ($this->entry) {
            $this->resourceHandle = $this->entry->getTable();
            if ($this->entry->exists) {
                $this->entryId = $this->entry->id;
            }
        }

        return array_keys(array_except(get_object_vars($this), ['entry']));
    }

    public function __wakeup()
    {
        if (! $this->resourceHandle) {
            return;
        }

        $resource = Resource::of($this->resourceHandle);

        if (! $this->entryId) {
            $this->entry = $resource->newEntryInstance();

            return;
        }

        $this->entry = $resource->loadEntry($this->entryId)->getEntry();
    }
}