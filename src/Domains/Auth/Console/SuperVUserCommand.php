<?php

namespace SuperV\Platform\Domains\Auth\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Auth\Contracts\Users;

class SuperVUserCommand extends Command
{
    protected $signature = 'superv:user {name} {email} {--role=user} {--password=}';

    protected $description = 'Create Platform User';

    public function handle()
    {
        if ($user = app(Users::class)->withEmail($this->argument('email'))) {
            $user->updatePassword($this->getPassword());
        } else {
            $user = app(Users::class)->create([
                'name'    => $this->argument('name'),
                'email'    => $this->argument('email'),
                'password' => bcrypt($this->getPassword()),
            ]);
        }

        $user->assign($this->option('role'));

        $user->allow('*');

        $this->comment("User created and allowed all (*) with ID: {$user->id}");
    }

    /**
     * @return array|mixed|string|null
     */
    protected function getPassword()
    {
        return $this->option('password') ?? $this->ask('Enter user password');
    }
}