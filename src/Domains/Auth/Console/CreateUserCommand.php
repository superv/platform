<?php

namespace SuperV\Platform\Domains\Auth\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Auth\Account;

class CreateUserCommand extends Command
{
    protected $signature = 'superv:create-user {email} {password} {--role=user} {--account=1}';

    protected $description = 'Create Platform User';

    public function handle()
    {
        $account = Account::query()->findOrFail(($this->option('account')));

        $user = $account->users()->create([
            'email'    => $this->argument('email'),
            'password' => bcrypt($this->argument('password')),
        ]);

        $user->assign($this->option('role'));

        $this->comment("User created with ID: {$user->id}");
    }
}