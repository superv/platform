<?php

namespace SuperV\Platform\Console;

use Exception;
use SuperV\Platform\Console\Jobs\InstallSuperV;
use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Auth\Users;

class InstallSuperVCommand extends Command
{
    protected $signature = 'superv:install {--hostname=}';

    protected $description = 'Install SuperV Platform';

    public function handle()
    {
        $this->comment('Installing SuperV');

        if (! $hostname = $this->option('hostname')) {
            $hostname = $this->ask("Please enter your project's hostname ", 'localhost');
        }

        try {
            InstallSuperV::dispatch(compact('hostname'));

            $this->comment("SuperV installed..! \n");

            if (! $this->option('no-interaction')) {
                $this->setup();
            }

            return;

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function setup()
    {
        if (! env('JWT_SECRET')) {
            if ($this->confirm('Looks like no JWT_SECRET set in your env file, shall we generate one now ?')) {
                $this->comment('Generating JWT Secret');
                $this->call('jwt:secret');
            }
        }

        if ($this->confirm('Would you like to create a user with full access now ?')) {
            $name = $this->ask('Enter the name for user');
            $email = $this->ask('Enter the email for user');
            $password = $this->ask('Enter the password');

            $user = Users::resolve()->create([
                'name'     => $name,
                'email'    => $email,
                'password' => bcrypt($password),
            ]);

            $user->assign('user');
            $user->allow('*');

            $this->comment("A user with full access was with ID: {$user->id}");
        } elseif ($this->confirm("Without any user setup, you will not be able to login to admin panel. \n Would you like to grant access to an existing user now?")) {
            $user = null;
            while (is_null($user)) {
                $email = $this->ask('Enter the email for existing user');

                if (! $user = Users::resolve()->withEmail($email)) {
                    $tryAgain = $this->confirm("User not found with email [".$email."], try again?");
                    if (! $tryAgain) {
                        $user = false;
                    }
                }
            }

            if ($user) {
                $user->assign('user');
                $user->allow('*');

                $this->comment("Granted full access to user with email [".$email."]");
            }
        }
    }
}
