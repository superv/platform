<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListConfigHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class UsersList implements ListConfigHook
{
    public static $identifier = 'platform.users.lists:default';

    public function config(TableInterface $table, IndexFields $fields)
    {
        $fields->show('email');
    }
}
