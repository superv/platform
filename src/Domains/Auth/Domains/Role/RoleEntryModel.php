<?php namespace SuperV\Platform\Domains\Auth\Domains\Role;

use SuperV\Platform\Domains\Entry\EntryModel;

class RoleEntryModel extends EntryModel
{
    protected $table = 'auth_roles';

    protected $fields = [
        'name:text|required|unique',
        'guard_name:text',
    ];
}