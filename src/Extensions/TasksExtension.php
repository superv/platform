<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;

class TasksExtension implements ExtendsResource
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $indexFields = $resource->indexFields();
        $indexFields->show('status');
        $indexFields->show('created_at');
//        $resource->getField('status')->copyToFilters(['default_value' => 'pending']);

        $resource->onIndexPage(function () use ($resource) {
        });

        $resource->onIndexConfig(function (ResourceTable $table) use ($resource) {
            $table->orderByLatest();
        });
    }

    public function extends(): string
    {
        return 'sv_tasks';
    }
}
