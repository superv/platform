<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Action\DeleteEntryAction;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;

class ResourceExtension implements ExtendsResource
{
    public function extend(Resource $resource)
    {
        $resource->searchable(['handle']);
        $fields = $resource->indexFields();
        $fields->get('addon')->copyToFilters();

        $resource->on('index.config', function (ResourceTable $table) {
            $table->addRowAction(DeleteEntryAction::class);
        });
    }

    public function extends(): string
    {
        return 'sv_resources';
    }
}