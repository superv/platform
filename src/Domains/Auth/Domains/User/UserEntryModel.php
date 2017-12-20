<?php namespace SuperV\Platform\Domains\Auth\Domains\User;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use SuperV\Modules\Nucleo\Domains\Entry\Nucleo;
use SuperV\Platform\Domains\Entry\EntryModel;

class UserEntryModel extends EntryModel
{
    protected $table = 'auth_users';

    protected $relationships = ['roles', 'permissions'];

    protected $fields = [
        'name:text|required',
        'email:text|required|unique',
        'roles:relation' => [
            'related' => Role::class,
            'multiple' => true
        ]  ,
        'permissions:relation' => [
            'related' => Permission::class,
            'multiple' => true,
        ]
    ];

    public function getTable()
    {
        return config('auth.providers.users.table');
    }
}