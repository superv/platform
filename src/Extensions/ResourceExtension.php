<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Resource;

class ResourceExtension
{
    public function extend(Resource $resource)
    {
//        $resource->config()->entryLabelField('identifier');
//        $resource->searchable(['identifier']);
//        $fields = $resource->indexFields();
//
//        $fields->get('namespace')->copyToFilters();

//        $resource->onIndexConfig(function (ResourceTable $table) {
//            $table->showIdColumn();
//        });
//
//        $resource->onIndexData(function (ResourceTable $table) {
//            $table->setOption('limit', 10);
//        });
    }

    public function extends(): string
    {
//        return 'sv_resources';
    }
}
