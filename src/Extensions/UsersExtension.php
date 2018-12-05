<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;

class UsersExtension implements ExtendsResource
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource)
    {
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