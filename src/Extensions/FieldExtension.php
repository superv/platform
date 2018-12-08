<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;

class FieldExtension implements ExtendsResource
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $resource->searchable(['name']);
        $fields = $resource->indexFields();
        $fields->getField('resource')->copyToFilters();
        $fields->getField('type')->copyToFilters();
    }

    public function extends(): string
    {
        return 'sv_fields';
    }
}