<?php

namespace SuperV\Platform\Domains\Auth;

use App\Notifications\ResetPassword;
use Current;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use SuperV\Platform\Domains\Auth\Access\HasActions;
use SuperV\Platform\Domains\Auth\Contracts\User as UserContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property mixed email
 */
class User extends Model implements UserContract, AuthenticatableContract, JWTSubject, CanResetPasswordContract
{
    use Authenticatable;
    use Notifiable;
    use HasActions;

    protected $table = 'users';

    protected $guarded = [];

    protected $visible = ['id', 'email', 'name'];

    protected $casts = [
        'ports' => 'json',
    ];

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

    public function account()
    {
        return $this->belongsTo(Account::class);
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