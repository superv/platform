<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Exception;
use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\AddonCollection;

class AddonReinstallCommand extends Command
{
    protected $signature = 'addon:reinstall {--identifier=} {--seed}';

    public function handle(AddonCollection $addons)
    {
        if (! $identifier = $this->option('identifier')) {
            if ($addons->enabled()->isEmpty()) {
                return $this->warn('There are no addons currently installed');
            }
            $identifier = $this->choice('Select Addon to Reinstall', $addons->enabled()->identifiers()->all());
        }

        try {
            $addon = $addons->withSlug($identifier);

            $this->call('addon:uninstall', ['--identifier' => $identifier]);

            $this->call('addon:install', [
                'path'   => $addon->path(),
                '--seed' => $this->option('seed'),
            ]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
