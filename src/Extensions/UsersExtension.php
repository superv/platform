<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;

class UsersExtension implements ExtendsResource
{
    public function extend(Resource $resource)
    {
        $resource->searchable(['email']);

        $resource->onIndexConfig(function (ResourceTable $table) {
        });

        $fields = $resource->indexFields();
        $fields->show('email');

    }

    public function extends(): string
    {
        return 'users';
    }
}
