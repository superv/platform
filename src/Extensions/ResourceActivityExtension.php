<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Action\DeleteEntryAction;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;
use SuperV\Platform\Domains\UI\Page\ResourcePage;

class ResourceActivityExtension implements ExtendsResource
{
    public function extend(Resource $resource)
    {
        $resource->on('index.page', function (ResourcePage $page) {
        });

        $resource->on('index.config', function (ResourceTable $table) {
            $table->addRowAction(DeleteEntryAction::class);
        });
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