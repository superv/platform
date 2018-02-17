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
            $this->comment(sprintf('Installing %s', $this->argument('slug')));
            $installer->setCommand($this)
                      ->path($this->option('path'))
                      ->slug($this->argument('slug'))
                      ->install();

            $this->comment(sprintf("Droplet %s installed \n", $this->argument('slug')));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }


    }
}