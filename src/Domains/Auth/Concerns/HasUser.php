<?php

namespace SuperV\Platform\Domains\Auth\Concerns;

/**
 * Trait HasUser
 *
 * @package SuperV\Platform\Domains\Auth
 * @property \SuperV\Platform\Domains\Auth\User $user
 */
trait HasUser
{
    public function email()
    {
        return $this->user->email;
    }

    public function user()
    {
        return $this->belongsTo(config('superv.auth.user.model'));
    }

    public function getUserId()
    {
        return $this->user->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->user->getAuthIdentifier();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->user->getAuthPassword();
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        return $this->user->getRememberToken();
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->user->setRememberToken($value);
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return $this->user->getRememberTokenName();
    }
}