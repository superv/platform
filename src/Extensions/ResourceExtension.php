<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceExtension implements ExtendsResource
{
    public function extend(Resource $resource)
    {
        $resource->searchable(['handle']);
        $fields = $resource->indexFields();
        $fields->get('addon')->copyToFilters();
    }

    public function extends(): string
    {
        return 'sv_resources';
    }
}