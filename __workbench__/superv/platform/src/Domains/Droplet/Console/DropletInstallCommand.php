<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Droplet\Installer;

class DropletInstallCommand extends Command
{
    protected $signature = 'droplet:install {slug} {--path=}';

    public function handle(Installer $installer)
    {
        try {
            $installer->path($this->option('path'))
                      ->slug($this->argument('slug'))
                      ->install();

            $this->comment('Droplet installed..!');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }


    }
}