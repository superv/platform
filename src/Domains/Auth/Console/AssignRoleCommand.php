<?php

namespace SuperV\Platform\Domains\Auth\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Auth\User;

class AssignRoleCommand extends Command
{
    protected $signature = 'superv:assign-role {email} {role} ';

    protected $description = 'Assign a role for Platform User';

    public function handle()
    {
        /** @var \SuperV\Platform\Domains\Auth\Contracts\User $user */
        $user = User::query()->where('email', $this->argument('email'))->firstOrFail();

        $user->assign($this->argument('role'));

        $roles = implode(',', $user->roles->pluck('slug')->toArray());

        $this->comment("Current roles: [{$roles}] ");
    }
}