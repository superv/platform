<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Auth\Concerns\HasRoles;
use SuperV\Platform\Domains\Auth\Contracts\User as UserContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements UserContract, AuthenticatableContract, JWTSubject
{
    use Authenticatable;
    use HasRoles;

    protected $table = 'users';

    protected $guarded = [];

    protected $visible = ['email', 'type'];

    protected $casts = [
        'ports' => 'json',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function createProfile(array $attributes)
    {
        return $this->profile()->create($attributes);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}