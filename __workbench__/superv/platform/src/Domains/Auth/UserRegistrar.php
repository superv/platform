<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Validation\Factory;
use SuperV\Platform\Domains\Auth\Events\UserCreatedEvent;

class UserRegistrar
{
    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $factory;

    /** @var \SuperV\Platform\Domains\Auth\Contracts\User */
    protected $user;

    /** @var array */
    protected $rules = [
        'email'    => 'required|email|unique:users',
        'password' => 'required|confirmed|min:6',
        'type'     => 'required',
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

    public function register(array $request)
    {
        $this->validate($request);

        $this->create($request);

        event(new UserCreatedEvent($this->user, $request));
    }

    /**
     * Validate request
     *
     * @param array $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate(array $request)
    {
        $this->factory->make($request, $this->rules)->validate();
    }

    /**
     * Create the user
     *
     * @param array $request
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    protected function create(array $request)
    {
        $this->user = User::create([
            'email'    => $request['email'],
            'password' => bcrypt($request['password']),
            'type'     => $request['type'],
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
}