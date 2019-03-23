<?php

namespace SuperV\Platform\Domains\Auth;

use Current;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use SuperV\Platform\Domains\Auth\Access\HasActions;
use SuperV\Platform\Domains\Auth\Contracts\User as UserContract;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends ResourceEntry implements
    UserContract, AuthenticatableContract, JWTSubject, CanResetPasswordContract
{
    use Authenticatable;
    use Notifiable;
    use HasActions;

    protected $table = 'users';

    protected $guarded = [];

    protected $visible = ['id', 'email', 'name'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (User $user) {
            $user->roles()->sync([]);
            $user->actions()->sync([]);

            optional($user->profile)->delete();
        });
    }

    public function getId()
    {
        return $this->getKey();
    }

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function updatePassword($newPassword)
    {
        $this->update(['password' => bcrypt($newPassword)]);
    }

    public function createProfile(array $attributes)
    {
        return $this->profile()->create($attributes);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function verifyPassword($checkPassword)
    {
        return \Hash::check($checkPassword, $this->password);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
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

        return ['port' => optional(Current::port())->slug()];
    }
}