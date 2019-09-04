<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\Jobs\UninstallAddonJob;

class AddonUninstallCommand extends Command
{
    protected $signature = 'addon:uninstall {--namespace=}';

    public function handle()
    {
        if (! $namespace = $this->option('namespace')) {
            $namespace = $this->choice('Select Addon to Uninstall', sv_addons()->enabled()->slugs()->all());
        }
        $this->comment(sprintf('Uninstalling %s', $namespace));

        if ($this->dispatch(new UninstallAddonJob($namespace))) {
            $this->info('The ['.$namespace.'] addon successfully uninstalled.');
        } else {
            $this->error('Addon could not be uninstalled');
        }
    }
}