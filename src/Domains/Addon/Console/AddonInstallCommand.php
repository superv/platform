<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Domains\Addon\Locator;
use SuperV\Platform\Exceptions\ValidationException;

class AddonInstallCommand extends Command
{
    protected $signature = 'addon:install {namespace} {--path=} {--seed}';

    public function handle(Installer $installer)
    {
        try {
            $this->comment(sprintf('Installing %s', $this->argument('namespace')));
            $installer->setCommand($this);
            $installer->setNamespace($this->argument('namespace'));

            if ($this->option('path')) {
                $installer->setPath($this->option('path'));
            } else {
                $installer->setLocator(new Locator());
            }

            $installer->install();

            if ($this->option('seed')) {
                $installer->seed();
            }

            $this->comment(sprintf("Addon %s installed \n", $this->argument('namespace')));
        } catch (ValidationException $e) {
            $this->error($e->getErrorsAsString());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
