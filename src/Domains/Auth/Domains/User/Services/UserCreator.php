<?php

namespace SuperV\Platform\Domains\Auth\Domains\User\Services;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Auth\Domains\User\Users;

class UserCreator
{
    use DispatchesJobs;

    protected $name;

    protected $email;

    protected $password;

    protected $user;

    /**
     * @var Users
     */
    protected $users;

    public function __construct(Users $users)
    {
        $this->users = $users;
    }

    public function create()
    {
        $this->user = $this->users->create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => $this->password,
        ]);
        if ($this->user) {
            event(new Registered($this->user));
        } else {
            throw new UserCreationException("User can not be created");
        }

        return $this;
    }

    /**
     * @param mixed $name
     *
     * @return UserCreator
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed $email
     *
     * @return UserCreator
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param mixed $password
     *
     * @return UserCreator
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param mixed $user
     *
     * @return UserCreator
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}