<?php

namespace SuperV\Platform\Domains\Auth;

use App\Notifications\ResetPassword;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use SuperV\Platform\Domains\Auth\Contracts\User as UserContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property mixed email
 */
class User extends Model implements UserContract, AuthenticatableContract, JWTSubject, CanResetPasswordContract
{
    use Authenticatable;
    use Notifiable;

    protected $table = 'users';

    protected $guarded = [];

    protected $visible = ['id', 'email', 'type'];

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
     * @param  string  $token
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

    public function verifyPassword($checkPassword) {

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

    public function getEmail()
    {
        return $this->email;
    }
}