<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Exception;
use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\AddonCollection;

class AddonReinstallCommand extends Command
{
    protected $signature = 'addon:reinstall {--addon=}';

    public function handle(AddonCollection $addons)
    {
        if (! $addon = $this->option('addon')) {
            $addon = $this->choice('Select Addon to Reinstall', sv_addons()->enabled()->slugs()->all());
        }

        try {
            $this->call('addon:uninstall', ['--addon' => $addon]);
        } catch (Exception $e) {
        }

        $this->call('addon:install', [
            'addon' => $addon,
        ]);
    }
}