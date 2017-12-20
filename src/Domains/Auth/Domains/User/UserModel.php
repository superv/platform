<?php namespace SuperV\Platform\Domains\Auth\Domains\User;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Spatie\Permission\Traits\HasRoles;
use SuperV\Platform\Domains\Entry\EntryModel;

class UserModel extends EntryModel implements AuthenticatableContract
{
    use Authenticatable, HasRoles;
    use Authorizable;

    public $timestamps = true;

    protected $table = 'auth_users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $visible = [
        'id',
        'name',
        'email',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}