<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Domains\Addon\Locator;

class AddonInstallCommand extends Command
{
    protected $signature = 'addon:install {slug} {--path=}';

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

            $this->comment(sprintf("Addon %s installed \n", $this->argument('slug')));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}