<?php

namespace SuperV\Platform\Domains\Auth\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Auth\Contracts\Users;

class CreateUserCommand extends Command
{
    protected $signature = 'superv:user {email} {--role=user}';

    protected $description = 'Create Platform User';

    public function handle()
    {
        $user = app(Users::class)->create([
            'email'    => $this->argument('email'),
            'password' => bcrypt($this->ask('Enter user password')),
        ]);

        $user->assign($this->option('role'));

        $user->allow('*');

        $this->comment("User created with ID: {$user->id}");
    }
}