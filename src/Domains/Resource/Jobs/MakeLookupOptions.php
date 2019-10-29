<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Resource;

class MakeLookupOptions
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var array
     */
    protected $queryParams;

    public function __construct(Resource $resource, array $queryParams = [])
    {
        $this->resource = $resource;
        $this->queryParams = $queryParams;
    }

    public function make()
    {
        $query = $this->makeQuery();

        if ($entryLabelField = $this->resource->fields()->getEntryLabelField()) {
            $query->orderBy($entryLabelField->getColumnName(), 'ASC');
        }

        return $query->get()
                     ->map(function (EntryContract $item) {
                         if ($keyName = $this->resource->config()->getKeyName()) {
                             $item->setKeyName($keyName);
                         }

                         return ['value' => $item->getId(),
                                 'text'  => sv_parse($this->getEntryLabel(), $item->toArray())];
                     })
                     ->all();
    }

    protected function makeQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->resource->newQuery();
        if ($this->queryParams) {
            $query->where($this->queryParams);
        }

        return $query;
    }

    protected function getEntryLabel()
    {
        $entryLabel = $this->resource->config()->getEntryLabel(sprintf("#%s", $this->resource->getKeyName()));

        return $entryLabel;
    }
}