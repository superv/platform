<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Action\DeleteEntryAction;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;

class UsersExtension implements ExtendsResource
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $resource->on('index.config', function (ResourceTable $table) {
            $table->addRowAction(DeleteEntryAction::class);
        });

        $fields = $resource->indexFields();
        $fields->show('email');
        $fields->show('account')->copyToFilters(['default_value' => 1]);

        $resource->searchable(['email']);
    }

    public function extends(): string
    {
        return 'users';
    }
}