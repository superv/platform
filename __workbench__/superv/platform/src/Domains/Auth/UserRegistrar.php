<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Validation\Factory;
use SuperV\Platform\Domains\Auth\Events\UserCreatedEvent;

class UserRegistrar
{
    /**  @var \Illuminate\Validation\Factory */
    protected $factory;

    /** @var \SuperV\Platform\Domains\Auth\Contracts\User */
    protected $user;

    /** @var array */
    protected $request;

    /** @var array */
    protected $rules = [
        'email'    => 'required|email|unique:users',
        'password' => 'required|confirmed|min:6',
    ];

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Return all validation rules
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * Merge additional validation rules
     *
     * @param array $rules
     */
    public function addRules(array $rules)
    {
        $this->rules = array_merge($this->rules, $rules);
    }

    /**
     * Set the request data
     *
     * @param array $request
     *
     * @return $this
     */
    public function setRequest(array $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     *  Register a system use
     */
    public function register()
    {
        $this->validate();

        $this->create();

        $this->announce();
    }

    /**
     * Validate request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate()
    {
        $this->factory->make($this->request, $this->rules)->validate();
    }

    /**
     * Create the user
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    protected function create()
    {
        $this->user = User::create([
            'email'    => $this->request['email'],
            'password' => bcrypt($this->request['password']),
        ]);

        return $this;
    }

    /**
     * Return the registered user
     *
     * @return \SuperV\Platform\Domains\Auth\Contracts\User
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Dispatch an event upon successfull registration
     */
    protected function announce()
    {
        event(new UserCreatedEvent($this->user, $this->request));
    }
}