<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceActivityExtension implements ExtendsResource
{
    public function extend(Resource $resource)
    {
        $fields = $resource->indexFields();
        $fields->show('entry');
        $fields->show('user')->copyToFilters(['query' => ['account_id' => 1]]);
        $fields->show('resource')->copyToFilters();

        $resource->searchable(['email']);
    }

    public function extends(): string
    {
        return 'sv_activities';
    }
}