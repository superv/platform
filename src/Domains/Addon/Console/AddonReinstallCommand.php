<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Exception;
use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\AddonCollection;

class AddonReinstallCommand extends Command
{
    protected $signature = 'addon:reinstall {--namespace=} {--seed}';

    public function handle(AddonCollection $addons)
    {
        if (! $namespace = $this->option('namespace')) {
            $namespace = $this->choice('Select Addon to Reinstall', $addons->enabled()->slugs()->all());
        }

        try {
            $this->call('addon:uninstall', ['--namespace' => $namespace]);

            $this->call('addon:install', [
                'namespace'  => $namespace,
                '--seed' => $this->option('seed'),
            ]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
