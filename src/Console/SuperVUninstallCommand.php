<?php

namespace SuperV\Platform\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Console\Jobs\EnvFile;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Addon\Jobs\UninstallAddonJob;
use SuperV\Platform\Domains\Resource\Support\PlatformBlueprints;

class SuperVUninstallCommand extends Command
{
    protected $signature = 'superv:uninstall';

    protected $description = 'Uninstall SuperV Platform';

    public function handle(AddonCollection $addons)
    {
        $this->comment('Uninstalling SuperV');

        $addons->map(function (Addon $addon) {
            if ($addon->getIdentifier() === 'sv.platform') {
                return;
            }
            $this->comment('Uninstalling addon: ['.$addon->getIdentifier().']');
            UninstallAddonJob::dispatch($addon);
        });

        $this->call('migrate:rollback', ['--namespace' => 'sv.platform', '--force' => true]);

        EnvFile::load(base_path('.env'))
               ->set('SV_INSTALLED', 'false')
               ->write();

        PlatformBlueprints::dropTables();

        $this->comment("SuperV Uninstalled..! \n");
    }
}
