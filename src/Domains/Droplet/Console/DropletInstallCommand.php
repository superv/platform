<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Droplet\Locator;

class DropletInstallCommand extends Command
{
    protected $signature = 'droplet:install {slug} {--path=}';

    public function handle(Installer $installer)
    {
        try {
            $this->comment(sprintf('Installing %s', $this->argument('slug')));
            $installer->setCommand($this)
                      ->setSlug($this->argument('slug'));

            if ($this->option('path')) {
                $installer->setPath($this->option('path'));
            } else {
                $installer->setLocator(new Locator());
            }

            $installer->install();

            $this->comment(sprintf("Droplet %s installed \n", $this->argument('slug')));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}