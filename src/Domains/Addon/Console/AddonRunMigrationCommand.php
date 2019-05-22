<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\AddonModel;

class AddonRunMigrationCommand extends Command
{
    protected $signature = 'addon:migrate {--addon=}';

    public function handle()
    {
        if (! $addon = $this->option('addon')) {
            $addon = $this->choice('Select Addon to Run Migrations', sv_addons()->enabled()->slugs()->all());
        }

        $this->call('migrate', ['--addon' => $addon]);
    }
}