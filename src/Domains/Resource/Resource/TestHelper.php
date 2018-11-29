<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Resource\Resource;

class TestHelper
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function asOptions()
    {
        $options = $this->resource->newQuery()->get()->map(function($entry) {
            return ['value' => $entry->getId(), 'text' => $this->resource->getEntryLabel($entry)];
        })->all();

        return   array_merge([['value' => '', 'text' => $this->resource->getSingularLabel()]], $options);
    }
}