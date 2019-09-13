<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Exception;
use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Exceptions\ValidationException;

class AddonInstallCommand extends Command
{
    protected $signature = 'addon:install {identifier} {--type=module} {--path=} {--seed}';

    public function handle(Installer $installer)
    {
        try {
            $this->comment(sprintf('Installing %s', $this->argument('identifier')));
            $installer->setCommand($this);
            $installer->setIdentifier($this->argument('identifier'));

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

            $this->comment(sprintf("Addon %s installed \n", $this->argument('identifier')));
        } catch (ValidationException $e) {
            $this->error($e->getErrorsAsString());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
