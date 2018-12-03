<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceTable extends EntryTable
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function getFields()
    {
        return $this->resource->fields()->forTable();
    }

    public function getFilters(): Collection
    {
        return $this->resource->getFilters();
    }

    public function getQuery()
    {
        return $this->query ?? $this->resource->newQuery();
    }

    public function getDataUrl()
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        return sv_url($this->resource->route('index.table').'/data');
    }

    public function setResource(Resource $resource): ResourceTable
    {
        $this->resource = $resource;

        return $this;
    }
}