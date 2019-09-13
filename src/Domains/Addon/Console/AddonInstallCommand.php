<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Exception;
use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Exceptions\ValidationException;

class AddonInstallCommand extends Command
{
    protected $signature = 'addon:install {vendor} {package} {--identifier=} {--type=module} {--path=} {--seed}';

    public function handle(Installer $installer)
    {
        try {
            $this->comment('Installing '.$label = sprintf('%s/%s', $this->argument('vendor'), $this->argument('package')));
            $installer->setCommand($this);
            $installer->setVendor($this->argument('vendor'));
            $installer->setName($this->argument('package'));
            $installer->setAddonType($this->option('type'));

            if ($this->option('identifier')) {
                $installer->setIdentifier($this->option('identifier'));
            }
            if ($this->option('path')) {
                $installer->setPath($this->option('path'));
            }

            try {
                $installer->install();
            } catch (Exception $e) {
                dd($e);
            }

            if ($this->option('seed')) {
                $installer->seed();
            }

            $this->comment(sprintf("Addon %s installed \n", $label));
        } catch (ValidationException $e) {
            $this->error($e->getErrorsAsString());
        } catch (\Exception $e) {
            dd($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
