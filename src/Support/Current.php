<?php

namespace SuperV\Platform\Support;

use Carbon\Carbon;
use SuperV\Platform\Domains\Auth\Contracts\Users;
use SuperV\Platform\Domains\Port\Port;

class Current
{
    /**
     * @var \SuperV\Platform\Domains\Auth\Contracts\Users
     */
    protected $users;

    protected $user;

    protected $migrationScope;

    /**
     * Return the current logged in user
     *
     * @return \SuperV\Platform\Domains\Auth\Contracts\User
     */
    public function user()
    {
        if (! $this->user) {
            $this->user = app(Users::class)->find(auth()->guard()->id());
        }

        return $this->user;
    }

    /**
     * Return the current platform port
     */
    public function port(): ?Port
    {
        return \Platform::port();
    }

    /**
     * Return the current url of the active port
     */
    public function url(string $path = null): string
    {
        return $path ? url($path) : url()->current();
    }

    public function requestPath()
    {
      return str_replace_last('/'.Current::port()->prefix(), '', request()->getPathInfo());
    }

    /**
     * Return current application environment
     *
     * @return string
     */
    public function env()
    {
        return app()->environment();
    }

    /**
     * Return true if we are on local environment
     *
     * @return bool
     */
    public function envIsLocal()
    {
        return $this->env() === 'local';
    }

    /**
     * Return true if we are on testing environment
     *
     * @return bool
     */
    public function envIsTesting()
    {
        return $this->env() === 'testing';
    }

    /**
     * Return true if we are on production environment
     *
     * @return bool
     */
    public function envIsProduction()
    {
        return $this->env() === 'production';
    }

    /**
     * Return true if we are on console
     *
     * @return bool
     */
    public function isConsole()
    {
        return app()->runningInConsole();
    }

    public function time()
    {
        return Carbon::now();
    }

    public function setMigrationScope($scope)
    {
        $this->migrationScope = $scope;
    }

    public function migrationScope()
    {
        return $this->migrationScope;
    }
}