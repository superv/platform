<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

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

//    public function __construct(Resource $resource, array $queryParams = [])
//    {
//        $this->resource = $resource;
//        $this->queryParams = $queryParams;
//    }

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

    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): MakeLookupOptions
    {
        $this->resource = $resource;

        return $this;
    }

    public function setQueryParams(array $queryParams): MakeLookupOptions
    {
        $this->queryParams = $queryParams;

        return $this;
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