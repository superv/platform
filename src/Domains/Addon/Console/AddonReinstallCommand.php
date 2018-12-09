<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Exception;
use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\AddonCollection;

class AddonReinstallCommand extends Command
{
    protected $signature = 'addon:reinstall {addon}';

    public function handle(AddonCollection $addons)
    {
        $addon = $this->argument('addon');

        try {
            $this->call('addon:uninstall', ['--addon' => $addon]);
        } catch (Exception $e) {
        }

        $this->call('addon:install', [
            'addon' => $addon,
        ]);
    }
}