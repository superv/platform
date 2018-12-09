<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Domains\Addon\Locator;

class AddonInstallCommand extends Command
{
    protected $signature = 'addon:install {addon} {--path=}';

    public function handle(Installer $installer)
    {
        try {
            $this->comment(sprintf('Installing %s', $this->argument('addon')));
            $installer->setCommand($this)
                      ->setSlug($this->argument('addon'));

            if ($this->option('path')) {
                $installer->setPath($this->option('path'));
            } else {
                $installer->setLocator(new Locator());
            }

            $installer->install();

            $this->comment(sprintf("Addon %s installed \n", $this->argument('addon')));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}