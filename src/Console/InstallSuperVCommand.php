<?php

namespace SuperV\Platform\Console;

use Exception;
use File;
use SuperV\Platform\Console\Jobs\InstallSuperV;
use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Auth\Users;
use SuperV\Platform\Support\JsonFile;

class InstallSuperVCommand extends Command
{
    protected $signature = 'superv:install {--hostname=}';

    protected $description = 'Install SuperV Platform';

    protected $version = '0.20.x-dev';

    public function handle()
    {
        $this->comment('Installing SuperV');

        if (! $hostname = $this->option('hostname')) {
            $hostname = $this->ask("Please enter your project hostname", 'localhost');
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
        $this->setupDirectories();

        $this->setupJwtSecret();

        $this->setupUser();

        $this->setupComposer();
    }

    protected function setupComposer()
    {
        if (! File::isWritable(base_path('composer.json'))) {
            return;
        }

        $composer = JsonFile::fromPath(base_path('composer.json'));

        if (! $composer->get('extra.merge-plugin')) {
            if ($this->confirm("I will modify your main composer.json to add merge-plugin configuration. Is that OK?", true)) {
                $composer->merge('extra', [
                    'merge-plugin' => [
                        "include" => [
                            "addons/*/*/*/composer.json",
                        ],
                    ],
                ]);

                $composer->write();
            }
        }
    }

    protected function setupJwtSecret(): void
    {
        if (! env('JWT_SECRET')) {
            if ($this->confirm('Looks like no JWT_SECRET set in your env file, shall we generate one now ?', true)) {
                $this->comment('Generating JWT Secret');
                $this->call('jwt:secret');
            }
        }
    }

    protected function setupUser(): void
    {
        if ($this->confirm('Would you like to create a user with full access now ?', true)) {
            $name = $this->ask('Enter the name for user');
            $email = $this->ask('Enter the email for user');
            $confirmed = false;

            while ($confirmed === false) {
                $password = $this->secret('Enter the password');
                $confirmation = $this->secret('Re-Enter the password');
                $confirmed = ($password === $confirmation);

                if (! $confirmed) {
                    $this->warn('The passwords do not match. Please try again.');
                }
            }

            $user = Users::resolve()->create([
                'name'     => $name,
                'email'    => $email,
                'password' => bcrypt($password),
            ]);

            $user->assign('user');
            $user->allow('*');

            $this->comment("A user with full access was with ID: {$user->id}");
        } elseif ($this->confirm("Without any user setup, you will not be able to login to admin panel. \n Would you like to grant access to an existing user now?", true)) {
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

    protected function setupDirectories(): void
    {
        if (! File::isWritable(base_path())) {
            return;
        }

        if (! file_exists(base_path('addons'))) {
            File::makeDirectory(base_path('addons'), 0777);
            File::put(base_path('addons/.gitignore'), 'superv/*');
        }
    }
}
