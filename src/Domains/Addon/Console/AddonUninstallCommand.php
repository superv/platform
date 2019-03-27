<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\Jobs\UninstallAddonJob;

class AddonUninstallCommand extends Command
{
    protected $signature = 'addon:uninstall {--addon=}';

    public function handle()
    {
        if (! $addon = $this->option('addon')) {
            $addon = $this->choice('Select Addon to Uninstall', sv_addons()->enabled()->slugs()->all());
        }
        $this->comment(sprintf('Uninstalling %s', $addon));

        if ($this->dispatch(new UninstallAddonJob($addon))) {
            $this->info('The ['.$addon.'] addon successfully uninstalled.');
        } else {
            $this->error('Addon could not be uninstalled');
        }
    }
}