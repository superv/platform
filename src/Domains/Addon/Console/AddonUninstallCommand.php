<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Addon\Jobs\UninstallAddonJob;

class AddonUninstallCommand extends Command
{
    protected $signature = 'addon:uninstall {--identifier=}';

    public function handle(AddonCollection $addons)
    {
        if (! $identifier = $this->option('identifier')) {
            $identifier = $this->choice('Select Addon to Uninstall', $addons->enabled()->slugs()->all());
        }
        $this->comment(sprintf('Uninstalling %s', $identifier));

        if ($this->dispatch(new UninstallAddonJob($identifier))) {
            $this->info('The ['.$identifier.'] addon successfully uninstalled.');
        } else {
            $this->error('Addon could not be uninstalled');
        }
    }
}