<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;

class FieldExtension implements ExtendsResource
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $resource->searchable(['name']);

        $resource->on('index.config', function (ResourceTable $table) use ($resource) {
            $fields = $resource->indexFields();
            $fields->getField('resource')->copyToFilters();
            $fields->getField('type')->copyToFilters();
        });
    }

    public function extends(): string
    {
        return 'sv_fields';
    }
}