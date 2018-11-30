<?php

namespace SuperV\Platform\Domains\Resource\Filter;

class DistinctFilter extends SelectFilter
{
    public function getOptions(): array
    {
        return $this->resource->newQuery()
                              ->select($this->getIdentifier())
                              ->distinct()
                              ->where($this->getIdentifier(), '!=', null)
                              ->get()
                              ->pluck($this->getIdentifier(), $this->getIdentifier())
                              ->all();
    }
}