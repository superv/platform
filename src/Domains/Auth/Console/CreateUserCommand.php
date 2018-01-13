<?php

namespace SuperV\Platform\Domains\Auth\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Auth\Domains\User\Services\UserCreator;

class CreateUserCommand extends Command
{
    protected $signature = 'auth:user:create {name} {email} {password}';

    public function handle(UserCreator $creator)
    {
        $user = $creator->setName($this->argument('name'))
                        ->setEmail($this->argument('email'))
                        ->setPassword($this->argument('password'))
                        ->create()
                        ->getUser();

        $this->comment("User {$user->name} ({$user->email}) was created successfully");
    }
}