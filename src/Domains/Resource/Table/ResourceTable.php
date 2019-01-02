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
        return $this->resource->indexFields()->get();
    }

    public function getFilters(): Collection
    {
        return $this->resource->getFilters();
    }

    public function getQuery()
    {
        return $this->query ?? $this->resource->newQuery();
    }

    public function onQuerying($query)
    {
        $this->resource->fire('table.querying', ['query' => $query]);
    }

    public function getDataUrl()
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        return sv_url($this->resource->route('index.table').'/data');
    }

    protected function getRowKeyName()
    {
        return $this->resource->getConfigValue('key_name', 'id');
    }

    protected function getRowId($rowEntry)
    {
        return $rowEntry->getAttribute($this->getRowKeyName());
    }

    public function setResource(Resource $resource): ResourceTable
    {
        $this->resource = $resource;

        return $this;
    }
}