<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;

class ResourceExtension implements ExtendsResource
{
    public function extend(Resource $resource)
    {
        $resource->config()->entryLabelField('identifier');
        $resource->searchable(['identifier']);
        $fields = $resource->indexFields();

        $fields->get('namespace')->copyToFilters();

        $resource->onIndexConfig(function (ResourceTable $table) {
            $table->showIdColumn();
        });

        $resource->onIndexData(function (ResourceTable $table) {
            $table->setOption('limit', 10);
        });
    }

    public function extends(): string
    {
        return 'sv_resources';
    }
}
