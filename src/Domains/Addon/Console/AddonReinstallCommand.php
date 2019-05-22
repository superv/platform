<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Exception;
use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\AddonCollection;

class AddonReinstallCommand extends Command
{
    protected $signature = 'addon:reinstall {--addon=} {--seed}';

    public function handle(AddonCollection $addons)
    {
        if (! $addon = $this->option('addon')) {
            $addon = $this->choice('Select Addon to Reinstall', $addons->enabled()->slugs()->all());
        }

        try {
            $this->call('addon:uninstall', ['--addon' => $addon]);

            $this->call('addon:install', [
                'addon'  => $addon,
                '--seed' => $this->option('seed'),
            ]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}