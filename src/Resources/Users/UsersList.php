<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class UsersList implements ListResolvedHook
{
    public static $identifier = 'platform.users.lists:default';

    public function resolved(TableInterface $table, IndexFields $fields)
    {
        $fields->show('name')->searchable();
        $fields->show('email')->searchable();
    }
}
