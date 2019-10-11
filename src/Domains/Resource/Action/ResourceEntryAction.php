<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\RequiresEntry;
use SuperV\Platform\Domains\Resource\Contracts\RequiresResource;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceEntryAction extends Action implements RequiresResource, RequiresEntry
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var EntryContract */
    protected $entry;

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function setEntry(EntryContract $entry)
    {
        $this->entry = $entry;
    }

    public function getRequestUrl()
    {
        return $this->entry->router()->actions($this->getName());
    }
}