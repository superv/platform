<?php

namespace SuperV\Platform\Domains\Resource\Features;

use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

class UserType
{
    public function handle(EntryContract $entry, array $config)
    {
        if ($role = array_get($config, 'inline.params.role')) {
            if ($entry instanceof User) {
                $entry->assign('user');
                $entry->assign($role);
            }
        }
    }
}