<?php namespace SuperV\Platform\Domains\Auth\Domains\User;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Spatie\Permission\Traits\HasRoles;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Entry\EntryModel;

class UserModel extends EntryModel implements AuthenticatableContract
{
    use Authenticatable, HasRoles;
    use Authorizable;

    public $timestamps = true;

    protected $table = 'users';

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

    public function droplet()
    {
        return $this->belongsTo(DropletModel::class, 'droplet_id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}